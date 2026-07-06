<?php

namespace Tests\Unit\Domains\Contact\ManageContact\Api\Controllers;

use App\Models\Contact;
use App\Models\Vault;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\ApiTestCase;

class ContactControllerTest extends ApiTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_marks_a_contact_as_favorite(): void
    {
        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $user = $this->createUser(['write']);

        $vault = Vault::factory()->create([
            'account_id' => $user->account_id,
            'name' => 'Test Vault',
        ]);
        $vault = $this->setPermissionInVault($user, Vault::PERMISSION_EDIT, $vault);

        $contact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_favorite' => false,
            'listed' => true,
        ]);

        $response = $this->post("/api/vaults/{$vault->id}/contacts/{$contact->id}/favorite");

        $response->assertStatus(200);

        // Verify the contact was marked as favorite
        $contact->refresh();
        $this->assertTrue($contact->is_favorite);

        $response->assertJson([
            'data' => [
                'id' => $contact->id,
                'is_favorite' => true,
            ],
        ]);
    }

    /** @test */
    public function it_updates_a_contact_personal_note(): void
    {
        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $user = $this->createUser(['write']);

        $vault = Vault::factory()->create([
            'account_id' => $user->account_id,
            'name' => 'Test Vault',
        ]);
        $vault = $this->setPermissionInVault($user, Vault::PERMISSION_EDIT, $vault);

        $contact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'personal_note' => null,
            'listed' => true,
        ]);

        $personalNote = 'This is a personal note about Jane. She loves coffee and hiking.';

        $response = $this->put("/api/vaults/{$vault->id}/contacts/{$contact->id}/note", [
            'personal_note' => $personalNote,
        ]);

        $response->assertStatus(200);

        // Verify the personal note was updated
        $contact->refresh();
        $this->assertEquals($personalNote, $contact->personal_note);

        $response->assertJson([
            'data' => [
                'id' => $contact->id,
                'personal_note' => $personalNote,
            ],
        ]);
    }

    /** @test */
    public function it_filters_contacts_by_favorite_status(): void
    {
        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $user = $this->createUser(['read']);

        $vault = Vault::factory()->create([
            'account_id' => $user->account_id,
            'name' => 'Test Vault',
        ]);
        $vault = $this->setPermissionInVault($user, Vault::PERMISSION_VIEW, $vault);

        // Create favorite contact
        $favoriteContact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'is_favorite' => true,
            'listed' => true,
        ]);

        // Create non-favorite contact
        $nonFavoriteContact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Bob',
            'last_name' => 'Williams',
            'is_favorite' => false,
            'listed' => true,
        ]);

        // Test filtering by favorite=1 (favorites only)
        $response = $this->get("/api/vaults/{$vault->id}/contacts?favorite=1");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should only return the favorite contact
        $this->assertCount(1, $data);
        $this->assertEquals($favoriteContact->id, $data[0]['id']);
        $this->assertTrue($data[0]['is_favorite']);

        // Verify the non-favorite contact is not in the results
        $contactIds = array_column($data, 'id');
        $this->assertNotContains($nonFavoriteContact->id, $contactIds);
    }

    /** @test */
    public function it_removes_a_contact_from_favorites(): void
    {
        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $user = $this->createUser(['write']);

        $vault = Vault::factory()->create([
            'account_id' => $user->account_id,
            'name' => 'Test Vault',
        ]);
        $vault = $this->setPermissionInVault($user, Vault::PERMISSION_EDIT, $vault);

        $contact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'is_favorite' => true,
            'listed' => true,
        ]);

        $response = $this->delete("/api/vaults/{$vault->id}/contacts/{$contact->id}/favorite");

        $response->assertStatus(200);

        // Verify the contact is no longer a favorite
        $contact->refresh();
        $this->assertFalse($contact->is_favorite);

        $response->assertJson([
            'data' => [
                'id' => $contact->id,
                'is_favorite' => false,
            ],
        ]);
    }

    /** @test */
    public function it_toggles_contact_favorite_status(): void
    {
        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $user = $this->createUser(['write']);

        $vault = Vault::factory()->create([
            'account_id' => $user->account_id,
            'name' => 'Test Vault',
        ]);
        $vault = $this->setPermissionInVault($user, Vault::PERMISSION_EDIT, $vault);

        $contact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Diana',
            'last_name' => 'Prince',
            'is_favorite' => false,
            'listed' => true,
        ]);

        // Toggle from false to true
        $response = $this->patch("/api/vaults/{$vault->id}/contacts/{$contact->id}/favorite");

        $response->assertStatus(200);
        $contact->refresh();
        $this->assertTrue($contact->is_favorite);

        // Toggle from true to false
        $response = $this->patch("/api/vaults/{$vault->id}/contacts/{$contact->id}/favorite");

        $response->assertStatus(200);
        $contact->refresh();
        $this->assertFalse($contact->is_favorite);
    }

    /** @test */
    public function it_updates_contact_personal_note_to_null(): void
    {
        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $user = $this->createUser(['write']);

        $vault = Vault::factory()->create([
            'account_id' => $user->account_id,
            'name' => 'Test Vault',
        ]);
        $vault = $this->setPermissionInVault($user, Vault::PERMISSION_EDIT, $vault);

        $contact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Eve',
            'last_name' => 'Adams',
            'personal_note' => 'Existing note',
            'listed' => true,
        ]);

        // Clear the personal note
        $response = $this->put("/api/vaults/{$vault->id}/contacts/{$contact->id}/note", [
            'personal_note' => null,
        ]);

        $response->assertStatus(200);

        // Verify the personal note was cleared
        $contact->refresh();
        $this->assertNull($contact->personal_note);

        $response->assertJson([
            'data' => [
                'id' => $contact->id,
                'personal_note' => null,
            ],
        ]);
    }

    /** @test */
    public function it_filters_contacts_by_non_favorite_status(): void
    {
        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $user = $this->createUser(['read']);

        $vault = Vault::factory()->create([
            'account_id' => $user->account_id,
            'name' => 'Test Vault',
        ]);
        $vault = $this->setPermissionInVault($user, Vault::PERMISSION_VIEW, $vault);

        // Create favorite contact
        Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Frank',
            'last_name' => 'Miller',
            'is_favorite' => true,
            'listed' => true,
        ]);

        // Create non-favorite contact
        $nonFavoriteContact = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'is_favorite' => false,
            'listed' => true,
        ]);

        // Test filtering by favorite=0 (non-favorites only)
        $response = $this->get("/api/vaults/{$vault->id}/contacts?favorite=0");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should only return the non-favorite contact
        $this->assertCount(1, $data);
        $this->assertEquals($nonFavoriteContact->id, $data[0]['id']);
        $this->assertFalse($data[0]['is_favorite']);
    }

    /** @test */
    public function it_gets_contact_statistics(): void
    {
        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $user = $this->createUser(['read']);

        $vault = Vault::factory()->create([
            'account_id' => $user->account_id,
            'name' => 'Test Vault',
        ]);
        $vault = $this->setPermissionInVault($user, Vault::PERMISSION_VIEW, $vault);

        // Create contacts with various attributes
        Contact::factory()->create([
            'vault_id' => $vault->id,
            'is_favorite' => true,
            'personal_note' => 'Note 1',
            'listed' => true,
        ]);

        Contact::factory()->create([
            'vault_id' => $vault->id,
            'is_favorite' => true,
            'personal_note' => null,
            'listed' => true,
        ]);

        Contact::factory()->create([
            'vault_id' => $vault->id,
            'is_favorite' => false,
            'personal_note' => 'Note 2',
            'listed' => true,
        ]);

        $response = $this->get("/api/vaults/{$vault->id}/contacts/stats");

        $response->assertStatus(200);

        $response->assertJson([
            'total_contacts' => 3,
            'favorite_contacts' => 2,
            'contacts_with_notes' => 2,
        ]);
    }

    /** @test */
    public function it_gets_favorites_list(): void
    {
        Carbon::setTestNow(Carbon::create(2018, 1, 1));
        $user = $this->createUser(['read']);

        $vault = Vault::factory()->create([
            'account_id' => $user->account_id,
            'name' => 'Test Vault',
        ]);
        $vault = $this->setPermissionInVault($user, Vault::PERMISSION_VIEW, $vault);

        // Create favorite contacts
        $favorite1 = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Favorite',
            'last_name' => 'One',
            'is_favorite' => true,
            'listed' => true,
        ]);

        $favorite2 = Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Favorite',
            'last_name' => 'Two',
            'is_favorite' => true,
            'listed' => true,
        ]);

        // Create non-favorite contact
        Contact::factory()->create([
            'vault_id' => $vault->id,
            'first_name' => 'Not',
            'last_name' => 'Favorite',
            'is_favorite' => false,
            'listed' => true,
        ]);

        $response = $this->get("/api/vaults/{$vault->id}/contacts/favorites");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should return only the two favorite contacts
        $this->assertCount(2, $data);
        
        $contactIds = array_column($data, 'id');
        $this->assertContains($favorite1->id, $contactIds);
        $this->assertContains($favorite2->id, $contactIds);

        // All returned contacts should be favorites
        foreach ($data as $contact) {
            $this->assertTrue($contact['is_favorite']);
        }
    }
}
