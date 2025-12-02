# Filament v4 Migration Rules

This document outlines the key changes and migration rules when working with Filament v4.

## Namespace Changes

### Layout Components vs Input Components

**IMPORTANT:** In Filament v4, only **layout components** have been moved to `Filament\Schemas\Components`. **Input components** remain in `Filament\Forms\Components`.

**Layout Components (moved to Schemas):**
- Section
- Tabs
- Wizard
- Grid
- Fieldset
- etc.

**Input Components (remain in Forms):**
- TextInput
- Select
- Checkbox
- Repeater
- Placeholder
- DatePicker
- FileUpload
- etc.

**Before (v3):**
```php
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
```

**After (v4):**
```php
use Filament\Schemas\Components\Section;  // Layout component moved
use Filament\Forms\Components\TextInput;   // Input component stays
use Filament\Forms\Components\Repeater;    // Input component stays
```

### Schema Class
The Schema class is now under `Filament\Schemas` instead of `Filament\Forms`.

**Before (v3):**
```php
use Filament\Forms\Form;

public static function form(Form $form): Form
{
    return $form->schema([...]);
}
```

**After (v4):**
```php
use Filament\Schemas\Schema;

public static function form(Schema $schema): Schema
{
    return $schema->components([...]);
}
```

## Common Migration Patterns

### Resource Forms
**v3 Pattern:**
```php
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('name'),
        ]);
}
```

**v4 Pattern:**
```php
use Filament\Schemas\Schema;
use Filament\Schemas\Components\TextInput;

public static function form(Schema $schema): Schema
{
    return $schema
        ->components([
            TextInput::make('name'),
        ]);
}
```

### Separated Form Classes
In this project, we use separated form classes for better organization:

```php
namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        TextInput::make('name'),
                        TextInput::make('email'),
                    ]),
            ]);
    }
}
```

## Quick Fix Checklist

When encountering namespace errors, check:

1. ✅ All `Filament\Forms\Components\*` imports changed to `Filament\Schemas\Components\*`
2. ✅ All `Filament\Forms\Form` changed to `Filament\Schemas\Schema`
3. ✅ Method `->schema([])` changed to `->components([])` for Schema class
4. ✅ Form method signature uses `Schema $schema` instead of `Form $form`

## Table Components
Table components remain in `Filament\Tables\Columns\*` (no change from v3).

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
```

## Actions

### All Actions Use Same Namespace
In Filament v4, ALL actions (page actions, table actions, etc.) use the same namespace:

```php
use Filament\Actions\Action;           // For custom actions
use Filament\Actions\EditAction;       // For edit actions
use Filament\Actions\DeleteAction;     // For delete actions
use Filament\Actions\BulkActionGroup;  // For bulk actions
```

**IMPORTANT:** There is NO `Filament\Tables\Actions\Action` in v4!

**Before (v3):**
```php
use Filament\Tables\Actions\Action;  // For table row actions
use Filament\Actions\Action;         // For page actions
```

**After (v4):**
```php
use Filament\Actions\Action;  // For ALL actions (both table and page)
```

## Resources
Resource class structure remains the same:
```php
use Filament\Resources\Resource;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function form(Schema $schema): Schema { ... }
    public static function table(Table $table): Table { ... }
}
```

## Migration Command

When migrating from v3 to v4, you can use find-and-replace:

```bash
# Replace Forms namespace with Schemas
find app/Filament -type f -name "*.php" -exec sed -i '' 's/Filament\\Forms\\Components/Filament\\Schemas\\Components/g' {} +

# Replace Form class with Schema class
find app/Filament -type f -name "*.php" -exec sed -i '' 's/use Filament\\Forms\\Form/use Filament\\Schemas\\Schema/g' {} +
```

## Common Errors and Solutions

### Error: Class "Filament\Forms\Components\Section" not found
**Solution:** Change namespace from `Filament\Forms\Components` to `Filament\Schemas\Components`

### Error: Method schema() does not exist on Schema
**Solution:** Use `components()` instead of `schema()` on Schema class

### Error: Argument type mismatch (Form vs Schema)
**Solution:** Update method signature to use `Schema $schema` instead of `Form $form`

## References

- [Filament v4 Official Documentation](https://filamentphp.com/docs)
- [Filament v4 Upgrade Guide](https://filamentphp.com/docs/upgrade-guide)
