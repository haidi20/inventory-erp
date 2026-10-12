-- Stored Procedure untuk Sinkronisasi Buku Besar (MySQL 8.0+)
-- Menggunakan Window Function (SUM OVER) untuk performa maksimal.

DELIMITER //

CREATE PROCEDURE SyncGeneralLedgerByPeriod(
    IN p_closing_period_id BIGINT
)
BEGIN
    DECLARE v_branch_id BIGINT;

    -- 1. Dapatkan Branch ID dari tabel closing_periods
    SELECT branch_id INTO v_branch_id 
    FROM closing_periods 
    WHERE id = p_closing_period_id;

    -- 2. Hapus data lama Buku Besar untuk Cabang ini
    DELETE FROM general_ledgers 
    WHERE branch_id = v_branch_id;

    -- 3. Insert massal untuk SELURUH AKUN di cabang tersebut
    -- Menggunakan PARTITION BY account_id agar saldo berjalan terhitung per akun secara paralel
    INSERT INTO general_ledgers (
        branch_id, 
        account_id, 
        journal_id, 
        journal_line_id, 
        journal_date,
        reference_no,
        description, 
        debit, 
        credit, 
        balance, 
        is_reconciled, 
        created_at, 
        updated_at
    )
    SELECT 
        jl.branch_id, 
        jl.account_id, 
        j.id, 
        jl.id, 
        j.journal_date, 
        j.reference_no,
        j.description, 
        jl.debit, 
        jl.credit,
        -- KUNCI: SUM OVER PARTITION BY account_id + Saldo Awal (Opening)
        SUM(jl.debit - jl.credit) OVER (
            PARTITION BY jl.account_id 
            ORDER BY j.journal_date, j.id, jl.id
        ) + COALESCE(ab.opening_balance, 0) as running_balance,
        jl.is_reconciled, 
        NOW(), 
        NOW()
    FROM journal_lines jl
    JOIN journals j ON j.id = jl.journal_id
    LEFT JOIN account_balances ab ON ab.account_id = jl.account_id 
        AND ab.branch_id = jl.branch_id 
        AND ab.period = 'Opening'
    WHERE jl.branch_id = v_branch_id
      AND jl.deleted_at IS NULL
      AND j.deleted_at IS NULL
    ORDER BY jl.account_id, j.journal_date, j.id, jl.id;
END //

DELIMITER ;
