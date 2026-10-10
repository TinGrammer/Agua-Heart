-- Safe migration for existing Agua Heart data.
-- Adds payment metadata to orders without removing or altering existing data.

SET @payment_type_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'orders'
      AND column_name = 'payment_type'
);

SET @payment_type_sql := IF(@payment_type_exists = 0,
    'ALTER TABLE orders ADD COLUMN payment_type ENUM(''paid'',''credit'') NULL AFTER status',
    'SELECT 1');

PREPARE payment_type_stmt FROM @payment_type_sql;
EXECUTE payment_type_stmt;
DEALLOCATE PREPARE payment_type_stmt;

SET @amount_exists := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'orders'
      AND column_name = 'amount'
);

SET @amount_sql := IF(@amount_exists = 0,
    'ALTER TABLE orders ADD COLUMN amount DECIMAL(10,2) NULL AFTER payment_type',
    'SELECT 1');

PREPARE amount_stmt FROM @amount_sql;
EXECUTE amount_stmt;
DEALLOCATE PREPARE amount_stmt;
