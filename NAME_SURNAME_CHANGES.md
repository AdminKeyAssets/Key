# Name and Surname Field Combination

This change combines the separate `name` and `surname` fields into a single `full_name` field for both Admin and Investor entities.

## Migration Plan

1. **Initial Migration (2025_06_03_000001_combine_name_surname_fields.php)**
   - Adds a new `full_name` column to both tables
   - Populates `full_name` by combining existing name and surname values
   - Makes the original `name` and `surname` columns nullable (for backward compatibility)

2. **Transition Period**
   - All code has been updated to use the `full_name` field
   - Accessors and mutators have been added to ensure backward compatibility
   - When `full_name` is set, it automatically populates `name` and `surname`
   - When `full_name` is read, it returns either the column's value or combines `name` and `surname`

3. **Final Migration (2025_06_04_000001_remove_name_surname_fields.php)**
   - After ensuring everything works correctly, run this migration to remove the legacy fields
   - This migration should only be run after thorough testing

## Code Changes

1. **Models**
   - Updated `Admin` and `Investor` models with accessors and mutators
   - Added `full_name` to the fillable array in both models

2. **Controllers**
   - Updated filter logic in `InvestorController` and `AssetController`
   - Updated queries to search by `full_name` with backward compatibility
   - Updated sorting to use `full_name` first

3. **Frontend**
   - Updated Vue components to display and filter using `full_name`
   - Maintained backward compatibility for existing data

## Testing

Before running the final migration, ensure that:
- All admin and investor names display correctly
- Filtering by name works properly
- Creating new users and updating existing ones works properly

## Rollback

If issues are encountered, you can:
1. Roll back the final migration: `php artisan migrate:rollback`
2. Continue using the hybrid approach that supports both name formats
