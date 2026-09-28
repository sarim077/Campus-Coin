<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptScannerFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_form_offers_private_receipt_and_award_scanning(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)
            ->get(route('transactions.create'))
            ->assertOk()
            ->assertSee('Scan a receipt or award')
            ->assertSee('Read document')
            ->assertSee('The image is processed in your browser and is not uploaded.')
            ->assertSee('image/jpeg,image/png,image/webp');

        $this->assertStringContainsString('receipt-scanner-', $response->getContent());
    }
}
