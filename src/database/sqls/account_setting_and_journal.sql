-- ==========================================
-- TABLE: branches
-- ==========================================
CREATE TABLE IF NOT EXISTS "branches"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "short_name" varchar not null,
  "is_active" tinyint(1) not null default '1',
  "deleted_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "branches_short_name_unique" on "branches"("short_name");

-- ==========================================
-- TABLE: accounts
-- ==========================================
CREATE TABLE IF NOT EXISTS "accounts"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "level" integer not null default '3',
  "is_header" tinyint(1) not null default '0',
  "type" varchar not null,
  "type_saldo" varchar check("type_saldo" in('debit', 'credit')) not null default 'debit',
  "financial_statement" varchar check("financial_statement" in('balance_sheet', 'income_statement')) default null,
  "parent_id" integer,
  "is_active" tinyint(1) not null default '1',
  "deleted_at" datetime,
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("parent_id") references "accounts"("id") on delete set null,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "accounts_code_unique" on "accounts"("code");

-- ==========================================
-- TABLE: account_mappings
-- ==========================================
CREATE TABLE IF NOT EXISTS "account_mappings"(
  "id" integer primary key autoincrement not null,
  "key" varchar not null,
  "menu_name" varchar,
  "account_id" integer not null,
  "type_saldo" varchar check("type_saldo" in('debit', 'credit')) not null default 'debit',
  "note" text,
  "value_source" varchar,
  "deleted_at" datetime,
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("account_id") references "accounts"("id") on delete cascade,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "account_mappings_key_unique" on "account_mappings"("key");

-- ==========================================
-- TABLE: closing_periods
-- ==========================================
CREATE TABLE IF NOT EXISTS "closing_periods"(
  "id" integer primary key autoincrement not null,
  "branch_id" integer not null,
  "period" varchar not null,
  "name" varchar not null,
  "start_date" date not null,
  "end_date" date not null,
  "closed_at" date,
  "status" varchar not null default 'open',
  "deleted_at" datetime,
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("branch_id") references "branches"("id") on delete cascade,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "closing_periods_branch_id_period_unique" on "closing_periods"("branch_id", "period");

-- ==========================================
-- TABLE: journals
-- ==========================================
CREATE TABLE IF NOT EXISTS "journals"(
  "id" integer primary key autoincrement not null,
  "branch_id" integer not null,
  "transaction_code" varchar not null,
  "reference_no" varchar not null,
  "reference_model" varchar,
  "reference_type" varchar,
  "reference_id" integer not null,
  "journal_date" date not null,
  "description" varchar,
  "status" varchar not null default('posted'),
  "is_system" tinyint(1) not null default('1'),
  "deleted_at" datetime,
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  "closing_period_id" integer,
  foreign key("deleted_by") references users("id") on delete set null on update no action,
  foreign key("updated_by") references users("id") on delete set null on update no action,
  foreign key("created_by") references users("id") on delete set null on update no action,
  foreign key("branch_id") references branches("id") on delete cascade on update no action,
  foreign key("closing_period_id") references "closing_periods"("id") on delete set null
);
CREATE UNIQUE INDEX "journals_branch_id_reference_no_unique" on "journals"("branch_id", "reference_no");
CREATE UNIQUE INDEX "journals_branch_id_transaction_code_unique" on "journals"("branch_id", "transaction_code");
CREATE INDEX "journals_reference_type_reference_id_index" on "journals"("reference_type", "reference_id");

-- ==========================================
-- TABLE: journal_lines
-- ==========================================
CREATE TABLE IF NOT EXISTS "journal_lines"(
  "id" integer primary key autoincrement not null,
  "branch_id" integer not null,
  "journal_id" integer not null,
  "account_id" integer not null,
  "debit" numeric not null default '0',
  "credit" numeric not null default '0',
  "is_reconciled" tinyint(1) not null default '0',
  "is_processed" tinyint(1) not null default '0',
  "deleted_at" datetime,
  "created_by" integer,
  "updated_by" integer,
  "deleted_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("branch_id") references "branches"("id") on delete cascade,
  foreign key("journal_id") references "journals"("id") on delete cascade,
  foreign key("account_id") references "accounts"("id") on delete cascade,
  foreign key("created_by") references "users"("id") on delete set null,
  foreign key("updated_by") references "users"("id") on delete set null,
  foreign key("deleted_by") references "users"("id") on delete set null
);
