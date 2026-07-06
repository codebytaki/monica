# Modified Files - Manual Changes Required

These files need manual modifications in your Monica installation.

## 1. app/Models/Contact.php

**Location:** `app/Models/Contact.php`

### Changes Required:

#### In the `$fillable` array (around line 42), add:
```php
'is_favorite',
'personal_note',
```

**Full fillable array should include:**
```php
protected $fillable = [
    'vault_id',
    'gender_id',
    'pronoun_id',
    'first_name',
    'last_name',
    'middle_name',
    'nickname',
    'maiden_name',
    'can_be_deleted',
    'show_quick_facts',
    'template_id',
    'last_updated_at',
    'company_id',
    'job_position',
    'listed',
    'file_id',
    'religion_id',
    'vcard',
    'distant_uuid',
    'distant_etag',
    'distant_uri',
    'prefix',
    'suffix',
    'is_favorite',      // ADD THIS LINE
    'personal_note',    // ADD THIS LINE
];
```

#### In the `$casts` array (around line 72), add:
```php
'is_favorite' => 'boolean',
```

**Full casts array should include:**
```php
protected $casts = [
    'vault_id' => 'string',
    'can_be_deleted' => 'boolean',
    'listed' => 'boolean',
    'show_quick_facts' => 'boolean',
    'is_favorite' => 'boolean',    // ADD THIS LINE
    'last_updated_at' => 'datetime',
];
```

---

## 2. routes/api.php

**Location:** `routes/api.php`

### Changes Required:

**Add this import at the top** (if not already present):
```php
use App\Domains\Contact\ManageContact\Api\Controllers\ContactController;
```

**Inside the `Route::prefix('vaults/{vault}')->group(function () {` block**, add these routes:

```php
// contacts
Route::prefix('vaults/{vault}')->group(function () {
    Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('contacts/favorites', [ContactController::class, 'favorites'])->name('contacts.favorites');  // ADD THIS
    Route::get('contacts/stats', [ContactController::class, 'stats'])->name('contacts.stats');              // ADD THIS
    Route::get('contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    Route::post('contacts/{contact}/favorite', [ContactController::class, 'markFavorite'])->name('contacts.favorite.mark');           // ADD THIS
    Route::delete('contacts/{contact}/favorite', [ContactController::class, 'removeFavorite'])->name('contacts.favorite.remove');     // ADD THIS
    Route::patch('contacts/{contact}/favorite', [ContactController::class, 'toggleFavorite'])->name('contacts.favorite.toggle');      // ADD THIS
    Route::put('contacts/{contact}/note', [ContactController::class, 'updateNote'])->name('contacts.note.update');                    // ADD THIS
});
```

**Lines to ADD (6 new routes):**
1. `Route::get('contacts/favorites', ...)`
2. `Route::get('contacts/stats', ...)`
3. `Route::post('contacts/{contact}/favorite', ...)`
4. `Route::delete('contacts/{contact}/favorite', ...)`
5. `Route::patch('contacts/{contact}/favorite', ...)`
6. `Route::put('contacts/{contact}/note', ...)`

---

## 3. app/Http/Resources/ContactResource.php (Optional Enhancement)

**Location:** `app/Http/Resources/ContactResource.php`

### Changes Required:

**In the `toArray()` method**, ensure these fields are included in the return array:

```php
'is_favorite' => $this->is_favorite,
'personal_note' => $this->personal_note,
```

**Note:** These fields may already be included automatically if using `$this->resource->toArray()`. Verify the output includes these fields.

---

## Verification Steps

After making these changes:

1. **Check syntax:**
   ```bash
   php artisan about
   ```

2. **Run migrations:**
   ```bash
   php artisan migrate
   ```

3. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```

4. **Run tests:**
   ```bash
   php artisan test --filter=ContactControllerTest
   ```

5. **Check routes:**
   ```bash
   php artisan route:list | grep contact
   ```

Expected output should show all 6 new routes registered.

---

## Troubleshooting

### Issue: Routes not working
- Clear route cache: `php artisan route:clear`
- Verify the ContactController import is correct
- Check that routes are inside the `auth:sanctum` middleware group

### Issue: Fields not saving
- Run migrations: `php artisan migrate`
- Check that fields are in `$fillable` array
- Verify database columns exist: `php artisan db:table contacts --columns`

### Issue: Boolean not casting
- Ensure `'is_favorite' => 'boolean'` is in the `$casts` array
- Clear config cache: `php artisan config:clear`
