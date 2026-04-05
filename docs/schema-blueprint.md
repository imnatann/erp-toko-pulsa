# Schema Blueprint

## Prinsip desain

- pakai PostgreSQL sebagai target utama
- semua nominal uang disimpan sebagai `bigInteger` dalam rupiah terkecil agar konsisten
- transaksi utama dipisah dari status log dan catatan validasi
- relasi, index, dan audit field harus siap untuk reporting dan investigasi

## Tabel inti MVP

### Organisasi dan akses

#### `outlets`

- tujuan: unit operasional toko
- field penting: `code`, `name`, `address`, `phone`, `is_active`
- index: `code`, `is_active`

#### `roles`

- tujuan: master role operasional
- field penting: `name`, `guard_name`
- catatan: bisa memakai package permission saat implementasi

#### `users`

- tujuan: pengguna internal
- field penting: `outlet_id`, `name`, `email`, `phone`, `password`, `is_active`, `last_login_at`
- index: `outlet_id`, `email`, `is_active`

## Master bisnis

#### `customers`

- field penting: `outlet_id`, `name`, `phone`, `notes`
- index: `outlet_id`, `phone`

#### `service_categories`

- field penting: `code`, `name`, `service_type`
- contoh `service_type`: `pulsa`, `data`, `voucher`, `e_wallet`, `transfer`, `cash_out`

#### `digital_services`

- field penting: `service_category_id`, `code`, `name`, `provider`, `default_nominal_amount`, `default_fee_amount`, `is_active`, `requires_reference`, `requires_destination_name`
- index: `service_category_id`, `code`, `is_active`

#### `product_categories`

- field penting: `code`, `name`, `is_active`

#### `products`

- field penting: `product_category_id`, `sku`, `name`, `purchase_price_amount`, `selling_price_amount`, `minimum_stock`, `is_active`
- index: `sku`, `product_category_id`, `is_active`

#### `manual_channels`

- field penting: `code`, `name`, `channel_type`, `notes`, `is_active`

## Transaksi digital

#### `digital_transactions`

- tujuan: tiket utama transaksi digital semi-manual
- field penting:
  - `outlet_id`
  - `customer_id` nullable
  - `digital_service_id`
  - `manual_channel_id` nullable
  - `code`
  - `status`
  - `destination_account`
  - `destination_name` nullable
  - `nominal_amount`
  - `fee_amount`
  - `total_amount`
  - `cash_effect_amount` default `0`
  - `submitted_at`
  - `processed_at` nullable
  - `validated_at` nullable
  - `created_by`
  - `processed_by` nullable
  - `validated_by` nullable
  - `supervisor_approved_by` nullable
  - `operator_note` nullable
  - `validation_note` nullable
  - `external_reference` nullable
  - `requires_supervisor_approval` default false
- unique: `code`
- index: `outlet_id`, `digital_service_id`, `status`, `submitted_at`, `validated_at`, `processed_by`, `validated_by`
- audit field: timestamps + soft delete dipertimbangkan nanti bila ada kebutuhan koreksi non-destruktif

#### `digital_transaction_status_logs`

- tujuan: histori transisi status
- field penting: `digital_transaction_id`, `from_status`, `to_status`, `acted_by`, `acted_at`, `note`, `external_reference` nullable, `metadata` jsonb nullable
- index: `digital_transaction_id`, `to_status`, `acted_by`, `acted_at`

#### `manual_validation_notes`

- tujuan: catatan proses manual yang bisa lebih dari satu
- field penting: `digital_transaction_id`, `author_id`, `note_type`, `body`, `created_at`

#### `transaction_attachments`

- tujuan: bukti screenshot/struk/manual proof
- field penting: `digital_transaction_id`, `uploaded_by`, `disk`, `path`, `original_name`, `mime_type`, `size_bytes`, `attachment_type`

## POS dan inventory

#### `sales`

- field penting: `outlet_id`, `customer_id` nullable, `cash_session_id`, `code`, `status`, `subtotal_amount`, `discount_amount`, `total_amount`, `paid_amount`, `change_amount`, `payment_method`, `sold_by`, `sold_at`
- index: `outlet_id`, `code`, `status`, `sold_at`, `sold_by`

#### `sale_items`

- field penting: `sale_id`, `product_id`, `qty`, `unit_price_amount`, `discount_amount`, `line_total_amount`

#### `stocks`

- field penting: `outlet_id`, `product_id`, `on_hand_qty`, `reserved_qty`, `minimum_stock`
- unique: `outlet_id`, `product_id`

#### `stock_movements`

- field penting: `outlet_id`, `product_id`, `reference_type`, `reference_id`, `movement_type`, `qty`, `before_qty`, `after_qty`, `note`, `acted_by`, `acted_at`
- index: `outlet_id`, `product_id`, `movement_type`, `acted_at`

## Kas dan ledger

#### `cash_sessions`

- field penting: `outlet_id`, `opened_by`, `closed_by` nullable, `opened_at`, `closed_at` nullable, `opening_balance_amount`, `closing_balance_amount` nullable, `status`, `closing_note` nullable

#### `cash_transactions`

- field penting: `outlet_id`, `cash_session_id` nullable, `reference_type`, `reference_id`, `direction`, `transaction_type`, `amount`, `effective_at`, `note`, `created_by`
- index: `outlet_id`, `transaction_type`, `direction`, `effective_at`

#### `ledger_entries`

- field penting: `outlet_id`, `reference_type`, `reference_id`, `entry_date`, `entry_type`, `account_code`, `amount`, `description`, `created_by`
- index: `outlet_id`, `entry_date`, `entry_type`, `account_code`

## Audit

#### `audit_logs`

- field penting: `outlet_id` nullable, `user_id` nullable, `auditable_type`, `auditable_id`, `event`, `old_values` jsonb nullable, `new_values` jsonb nullable, `ip_address` nullable, `user_agent` nullable, `created_at`
- index: `auditable_type`, `auditable_id`, `event`, `user_id`, `created_at`

## State machine transaksi digital

| Dari | Ke | Aktor utama | Catatan | Side effect |
| --- | --- | --- | --- | --- |
| `draft` | `diproses` | `operator` | opsional | set `processed_at`, isi `processed_by` |
| `diproses` | `pending_validasi` | `operator` | opsional | tambah log status |
| `pending_validasi` | `berhasil` | `operator` atau `supervisor` | wajib | set `validated_at`, posting kas/ledger/laporan |
| `pending_validasi` | `gagal` | `operator` atau `supervisor` | wajib | set `validated_at`, tidak posting omzet final |
| `draft` | `dibatalkan` | `kasir`, `admin`, `supervisor` | wajib | tambah log status |
| `gagal` | `refund_manual` | `admin` atau `supervisor` | wajib + approval | catat pengembalian kas bila ada |

## Approval rules awal

- transfer atau cash out di atas batas outlet harus mengisi `requires_supervisor_approval`
- `refund_manual` harus disetujui `supervisor` atau `admin` senior
- edit nominal setelah submit harus dibatasi ke role tertentu dan selalu masuk audit log
- perubahan ke status final wajib menyimpan actor, waktu, catatan, dan referensi bila tersedia

## Checklist migration awal

- nama tabel mengikuti domain bisnis
- FK jelas dan tidak menyebabkan kehilangan jejak audit secara tidak sengaja
- index dibuat untuk status, tanggal, outlet, actor, dan kode transaksi
- unique key diterapkan pada kode transaksi dan kombinasi stok per outlet
- rollback aman untuk fase awal scaffold
