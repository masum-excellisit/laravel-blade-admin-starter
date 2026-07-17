<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Concerns\HasRevisions;
use App\Models\ContentBlock;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\Page;
use App\Models\PortfolioItem;
use App\Models\User;
use App\Support\Activity;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CmsPlatformFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_form_submission_stores_field_payload(): void
    {
        $form = Form::create([
            'name' => 'Contact Quote',
            'slug' => 'contact-quote',
            'success_message' => 'Thanks for reaching out.',
            'is_active' => true,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'label' => 'Name',
            'name' => 'name',
            'type' => 'text',
            'required' => true,
            'sort_order' => 10,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'label' => 'Budget',
            'name' => 'budget',
            'type' => 'select',
            'options' => ['under-5k', 'over-5k'],
            'required' => true,
            'sort_order' => 20,
        ]);

        $this->post(route('forms.submit', $form->slug), [
            'name' => 'Ada Lovelace',
            'budget' => 'over-5k',
        ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Thanks for reaching out.');

        $submission = FormSubmission::firstOrFail();

        $this->assertTrue($submission->form->is($form));
        $this->assertSame([
            'name' => 'Ada Lovelace',
            'budget' => 'over-5k',
        ], $submission->data);
    }

    public function test_forms_order_fields_and_cast_structured_payloads(): void
    {
        $form = Form::create([
            'name' => 'Contact',
            'slug' => 'contact',
            'success_message' => 'Thanks',
            'is_active' => true,
        ]);

        FormField::create([
            'form_id' => $form->id,
            'label' => 'Last',
            'name' => 'last',
            'options' => ['b'],
            'sort_order' => 20,
        ]);
        FormField::create([
            'form_id' => $form->id,
            'label' => 'First',
            'name' => 'first',
            'options' => ['a'],
            'required' => true,
            'sort_order' => 10,
        ]);
        $submission = $form->submissions()->create([
            'data' => ['first' => 'Ada'],
            'ip_address' => '203.0.113.5',
        ]);

        $this->assertInstanceOf(HasMany::class, $form->fields());
        $this->assertSame(['first', 'last'], $form->fields()->pluck('name')->all());
        $this->assertSame(['a'], $form->fields()->first()->options);
        $this->assertTrue($form->fields()->first()->required);
        $this->assertSame(['first' => 'Ada'], $submission->fresh()->data);
    }

    public function test_activity_log_records_current_user_request_and_subject(): void
    {
        $user = User::create([
            'name' => 'Editor',
            'email' => 'editor@example.com',
            'password' => 'password',
        ]);
        $page = Page::create([
            'title' => 'About',
            'slug' => 'about',
            'status' => 'draft',
        ]);

        $this->actingAs($user);
        request()->server->set('REMOTE_ADDR', '203.0.113.10');

        Activity::log('updated', $page, 'Updated page copy', ['field' => 'body']);

        $log = ActivityLog::firstOrFail();

        $this->assertInstanceOf(BelongsTo::class, $log->user());
        $this->assertInstanceOf(MorphTo::class, $log->subject());
        $this->assertTrue($log->user->is($user));
        $this->assertTrue($log->subject->is($page));
        $this->assertSame('203.0.113.10', $log->ip_address);
        $this->assertSame(['field' => 'body'], $log->properties);
    }

    public function test_revisions_capture_and_restore_model_attributes(): void
    {
        $user = User::create([
            'name' => 'Editor',
            'email' => 'revision-editor@example.com',
            'password' => 'password',
        ]);
        $page = RevisionablePage::create([
            'title' => 'Original',
            'slug' => 'original',
            'body' => 'Before',
            'status' => 'draft',
        ]);

        $this->actingAs($user);
        $revision = $page->recordRevision('before edit');

        $page->forceFill(['title' => 'Changed', 'body' => 'After'])->save();
        $page->restoreRevision($revision);

        $this->assertInstanceOf(MorphMany::class, $page->revisions());
        $this->assertInstanceOf(MorphTo::class, $revision->revisionable());
        $this->assertInstanceOf(BelongsTo::class, $revision->user());
        $this->assertSame($user->id, $revision->user_id);
        $this->assertSame('Original', $page->fresh()->title);
        $this->assertSame('Before', $page->fresh()->body);
    }

    public function test_content_blocks_helper_and_portfolio_scope(): void
    {
        ContentBlock::create([
            'name' => 'Hero',
            'key' => 'hero',
            'content' => 'Hero copy',
            'is_active' => true,
        ]);
        ContentBlock::create([
            'name' => 'Draft',
            'key' => 'draft',
            'content' => 'Draft copy',
            'is_active' => false,
        ]);

        PortfolioItem::create([
            'title' => 'Draft project',
            'slug' => 'draft-project',
            'status' => 'draft',
        ]);
        PortfolioItem::create([
            'title' => 'Future project',
            'slug' => 'future-project',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);
        PortfolioItem::create([
            'title' => 'Live project',
            'slug' => 'live-project',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->assertSame('Hero copy', block('hero'));
        $this->assertNull(block('draft'));
        $this->assertSame(['Live project'], PortfolioItem::published()->pluck('title')->all());
    }

    public function test_seeders_include_cms_platform_defaults_and_editor_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(SettingsSeeder::class);

        $editor = Role::findByName('editor');

        $this->assertTrue($editor->hasPermissionTo('faqs.create'));
        $this->assertTrue($editor->hasPermissionTo('team.delete'));
        $this->assertTrue($editor->hasPermissionTo('portfolio.edit'));
        $this->assertTrue($editor->hasPermissionTo('blocks.edit'));
        $this->assertTrue($editor->hasPermissionTo('forms.view'));
        $this->assertTrue($editor->hasPermissionTo('redirects.view'));
        $this->assertTrue($editor->hasPermissionTo('activity-logs.view'));
        $this->assertFalse($editor->hasPermissionTo('forms.create'));

        $this->assertDatabaseHas('settings', ['group' => 'analytics', 'key' => 'analytics_ga4_id', 'value' => '']);
        $this->assertDatabaseHas('settings', ['group' => 'maintenance', 'key' => 'maintenance_enabled', 'value' => '0']);
        $this->assertDatabaseHas('settings', ['group' => 'cookie', 'key' => 'cookie_enabled', 'value' => '1']);
        $this->assertDatabaseHas('settings', ['group' => 'notifications', 'key' => 'notify_contact_email', 'value' => 'hello@example.com']);
        $this->assertDatabaseHas('settings', ['group' => 'notifications', 'key' => 'notify_job_applications', 'value' => '1']);
    }
}

class RevisionablePage extends Page
{
    use HasRevisions;

    protected $table = 'pages';
}
