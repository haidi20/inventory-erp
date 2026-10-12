# Compare BE vs SRC (Belum Dibuat)

Tanggal cek: 2026-04-16

Sumber compare:
- BE: be/database/migrations, be/app/model, be/app/repository, be/app/service
- SRC: src/database/migrations, src/app/Models, src/app/Repositories, src/app/Services

## 1) Migration BE yang belum ada di SRC
- [x] create_balance_and_permission_tables
- [x] create_bom_tables
- [x] create_coa_and_assets
- [x] create_companies_table
- [x] create_erp_lookup_tables
- [x] create_ingredients_table
- [x] create_initial_balances
- [x] create_item_conversions
- [x] create_item_relational_prices
- [x] create_item_stok_table
- [x] create_item_supplier_relations
- [x] create_item_support_tables
- [x] create_lookup_tables
- [x] create_payment_terms_table
- [x] create_people_table
- [x] create_pos_legacy_tables
- [x] create_purchase_orders_table
- [x] create_purchase_returns_table
- [x] create_rbac_tables
- [x] create_sales_transaction_tables
- [x] create_stock_adjustments_table
- [x] create_stock_transfers_table
- [x] create_taxes_table
- [x] create_warehouses_table

## 2) Table BE yang belum ada padanan di SRC
- [x] assemblies (model BE: Assembly)
- [x] assembly_items (model BE: AssemblyItem)
- [x] assets (model BE: Asset)
- [x] bom_costs (model BE: BomCost)
- [x] bom_details (model BE: BomDetail)
- [x] boms (model BE: Bom)
- [x] coas (model BE: Coa)
- [x] companies (model BE: Company)
- [x] delivery_order_details (model BE: DeliveryOrderDetail)
- [x] delivery_orders (model BE: DeliveryOrder)
- [x] erp_lookup_sets (model BE: ErpLookupSet)
- [x] erp_lookup_value_details (model BE: ErpLookupValueDetail)
- [x] erp_lookup_values (model BE: ErpLookupValue)
- [x] file_audits (model BE: FileAudit)
- [x] file_variants (model BE: FileVariant)
- [x] files (model BE: File)
- [x] ingredients (model BE: Ingredient)
- [x] item_accounts (model BE: ProductAccount)
- [x] item_balances (model BE: ItemBalanceRecord)
- [x] item_conversions (model BE: ItemConversion)
- [x] item_custom_fields (model BE: ItemCustomField)
- [x] item_details (model BE: ItemDetail)
- [x] item_discount_rules (model BE: ItemDiscountRule)
- [x] item_discounts (model BE: ItemDiscount)
- [x] item_opening_balances (model BE: ItemOpeningBalance)
- [x] item_prices (model BE: ProductPrice)
- [x] item_stocks (model BE: ProductWarehouse)
- [x] item_stok (model BE: ItemStok)
- [x] item_suppliers (model BE: ItemSupplier)
- [x] item_units (model BE: ProductUnit)
- [x] item_uoms (model BE: ItemUom)
- [x] items (model BE: MasterProduct)
- [x] lookup_types (model BE: LookupType)
- [x] lookup_values (model BE: LookupValue)
- [x] opening_balance_records (model BE: OpeningBalanceHeader)
- [x] payment_terms (model BE: PaymentTerm)
- [x] people (model BE: SupplierRecord, MasterCustomer, Person)
- [x] person_addresses (model BE: PersonAddress)
- [x] person_balances (model BE: PersonBalance)
- [x] person_contacts (model BE: PersonContact)
- [x] person_custom_fields (model BE: PersonCustomField)
- [x] person_saldo_details (model BE: PersonSaldo)
- [x] price_levels (model BE: PriceLevel)
- [x] product_purchases (model BE: ProductPurchase)
- [x] product_quotation (model BE: ProductQuotation)
- [x] product_sales (model BE: ProductSale)
- [x] purchase_order_items (model BE: PurchaseOrderItem)
- [x] purchase_orders (model BE: PurchaseOrder)
- [x] purchase_return_payments (model BE: PurchaseReturnPayment)
- [x] recurring_journal_lines (model BE: RecurringJournalLine)
- [x] role_permissions (model BE: RolePermission)
- [x] sales_invoice_agings (model BE: SalesInvoiceAging)
- [x] sales_invoice_details (model BE: SalesInvoiceDetail)
- [x] sales_invoices (model BE: SalesInvoice)
- [x] sales_order_details (model BE: SalesOrderDetail)
- [x] sales_orders (model BE: SalesOrder)
- [x] shifts (model BE: Shift)
- [x] stock_adjustment_items (model BE: StockAdjustmentItem)
- [x] stock_adjustments (model BE: StockAdjustment)
- [x] stock_transfer_items (model BE: StockTransferItem)
- [x] stock_transfers (model BE: StockTransfer)
- [x] stocks (model BE: Stock)
- [x] taxes (model BE: Tax)
- [x] temp_lov_details (model BE: TempLookupDetail)
- [x] temp_lovs (model BE: TempLookup)
- [x] transaction_details (model BE: TransactionDetail)
- [x] transactions (model BE: Transaction)
- [x] units (model BE: Unit)
- [x] user_branches (model BE: UserBranch)
- [x] user_roles (model BE: UserRole)
- [x] user_warehouses (model BE: UserWarehouse)
- [x] warehouses (model BE: Warehouse)

## 3) Model BE yang belum ada padanan di SRC
- [x] Assembly
- [x] AssemblyItem
- [x] Asset
- [x] Bom
- [x] BomCost
- [x] BomDetail
- [x] Coa
- [x] Company
- [x] DeliveryOrder
- [x] DeliveryOrderDetail
- [x] ErpLookupSet
- [x] ErpLookupValue
- [x] ErpLookupValueDetail
- [x] File
- [x] FileAudit
- [x] FileVariant
- [x] Ingredient
- [x] ItemBalanceRecord
- [x] ItemConversion
- [x] ItemCustomField
- [x] ItemDetail
- [x] ItemDiscount
- [x] ItemDiscountRule
- [x] ItemOpeningBalance
- [x] ItemStok
- [x] ItemSupplier
- [x] ItemUom
- [x] LookupType
- [x] LookupValue
- [x] MasterCustomer
- [x] MasterProduct
- [x] OpeningBalanceHeader
- [x] PaymentTerm
- [x] Person
- [x] PersonAddress
- [x] PersonBalance
- [x] PersonContact
- [x] PersonCustomField
- [x] PersonSaldo
- [x] PriceLevel
- [x] ProductAccount
- [x] ProductPrice
- [x] ProductPurchase
- [x] ProductQuotation
- [x] ProductSale
- [x] ProductUnit
- [x] ProductWarehouse
- [x] PurchaseOrder
- [x] PurchaseOrderItem
- [x] PurchaseReturnPayment
- [x] RecurringJournalLine
- [x] RolePermission
- [x] SalesInvoice
- [x] SalesInvoiceAging
- [x] SalesInvoiceDetail
- [x] SalesOrder
- [x] SalesOrderDetail
- [x] Shift
- [x] Stock
- [x] StockAdjustment
- [x] StockAdjustmentItem
- [x] StockTransfer
- [x] StockTransferItem
- [x] SupplierRecord
- [x] Tax
- [x] TempLookup
- [x] TempLookupDetail
- [x] Transaction
- [x] TransactionDetail
- [x] Unit
- [x] UserBranch
- [x] UserRole
- [x] UserWarehouse
- [x] Warehouse

## 4) Repository BE yang belum ada padanan di SRC
- [x] accounting_repository.go
- [x] app_menu_repository.go
- [x] assembly_repository.go
- [x] auth_repository.go
- [x] bom_repository.go
- [x] company_repository.go
- [x] file_repository.go
- [x] image_repository.go
- [x] initial_balance_repository.go
- [x] item_conversion_repository.go
- [x] item_price_repository.go
- [x] lookup_erp_repository.go
- [x] lookup_repository.go
- [x] master_customer_repository.go
- [x] master_product_repository.go
- [x] payment_term_repository.go
- [x] permission_repository.go
- [x] person_repository.go
- [x] purchase_order_repository.go
- [x] purchase_repository.go
- [x] purchase_return_repository.go
- [x] role_repository.go
- [x] sales_repository.go
- [x] sales_transaction_repository.go
- [x] satusehat_repository.go
- [x] ship_repository.go
- [x] stock_balance_repository.go
- [x] stock_transfer_repository.go
- [x] tax_repository.go
- [x] transaction_detail_repository.go
- [x] unit_repository.go
- [x] warehouse_repository.go

## 5) Service BE yang belum ada padanan di SRC
- [x] accounting_service.go
- [x] app_menu_service.go
- [x] assembly_service.go
- [x] auth_service.go
- [x] bom_service.go
- [x] branch_service.go
- [x] company_service.go
- [x] export_service.go
- [x] file_service.go
- [x] image_service.go
- [x] initial_balance_service.go
- [x] item_conversion_service.go
- [x] item_price_service.go
- [x] lookup_erp_service.go
- [x] lookup_service.go
- [x] master_customer_service.go
- [x] master_product_service.go
- [x] opening_balance_service.go
- [x] payment_term_service.go
- [x] permission_service.go
- [x] person_service.go
- [x] purchase_order_service.go
- [x] purchase_return_service.go
- [x] purchase_service.go
- [x] role_service.go
- [x] sales_transaction_service.go
- [x] satusehat_service.go
- [x] stock_balance_service.go
- [x] stock_transfer_service.go
- [x] tax_service.go
- [x] unit_service.go
- [x] warehouse_service.go

## Catatan
- Compare ini berbasis naming convention dan pemetaan table dari method TableName() pada model BE.
- Beberapa item bisa jadi sudah ada di SRC dengan nama berbeda, jadi tetap perlu verifikasi manual per modul.

## 6) Testing yang Sudah Dibuat (Repo & Service)

### Repository
| name_file_testing | name_file |
|---|---|
| DashboardRepositoryTest.php | DashboardRepository.php |
| AccountingRepositoryTest.php | AccountingRepository.php |
| GeneralLedgerRepositoryTest.php | GeneralLedgerRepository.php |
| JournalRepositoryTest.php | JournalRepository.php |
| CompanyRepositoryTest.php | CompanyRepository.php |
| AppMenuRepositoryTest.php | AppMenuRepository.php |
| AuthRepositoryTest.php | AuthRepository.php |
| BranchRepositoryTest.php | BranchRepository.php |
| EmployeeRepositoryTest.php | EmployeeRepository.php |
| ItemConversionRepositoryTest.php | ItemConversionRepository.php |
| ItemPriceRepositoryTest.php | ItemPriceRepository.php |
| LookupRepositoryTest.php | LookupRepository.php |
| LookupSetRepositoryTest.php | LookupSetRepository.php |
| MasterCustomerRepositoryTest.php | MasterCustomerRepository.php |
| PaymentTermRepositoryTest.php | PaymentTermRepository.php |
| PersonRepositoryTest.php | PersonRepository.php |
| ProductRepositoryTest.php | ProductRepository.php |
| TaxRepositoryTest.php | TaxRepository.php |
| UnitRepositoryTest.php | UnitRepository.php |
| WarehouseRepositoryTest.php | WarehouseRepository.php |
| AssemblyRepositoryTest.php | AssemblyRepository.php |
| BomRepositoryTest.php | BomRepository.php |
| SalesTransactionRepositoryTest.php | SalesTransactionRepository.php |
| DatabaseBackupRepositoryTest.php | DatabaseBackupRepository.php (belum ada) |

### Service
| name_file_testing | name_file |
|---|---|
| AccountSettingServiceTest.php | AccountSettingService.php |
| JournalServiceTest.php | JournalService.php |
| SidebarNavigationMenuServiceTest.php | SidebarNavigationMenuService.php |
| DashboardServiceTest.php | DashboardService.php |
| AuthServiceTest.php | AuthService.php |
| AccountingServiceTest.php | AccountingService.php |
| GeneralLedgerServiceTest.php | GeneralLedgerService.php |
| CompanyServiceTest.php | CompanyService.php |
| AssemblyServiceTest.php | AssemblyService.php |
| BomServiceTest.php | BomService.php |
| BranchServiceTest.php | BranchService.php |
| EmployeeServiceTest.php | EmployeeService.php |
| ItemConversionServiceTest.php | ItemConversionService.php |
| ItemPriceServiceTest.php | ItemPriceService.php |
| LookupSetServiceTest.php | LookupSetService.php |
| LookupServiceTest.php | LookupService.php |
| MasterCustomerServiceTest.php | MasterCustomerService.php |
| MasterProductServiceTest.php | MasterProductService.php |
| PaymentTermServiceTest.php | PaymentTermService.php |
| PersonServiceTest.php | PersonService.php |
| ProductServiceTest.php | ProductService.php |
| TaxServiceTest.php | TaxService.php |
| UnitServiceTest.php | UnitService.php |
| WarehouseServiceTest.php | WarehouseService.php |
| SalesTransactionServiceTest.php | SalesTransactionService.php |
| UserServiceTest.php | UserService.php |
