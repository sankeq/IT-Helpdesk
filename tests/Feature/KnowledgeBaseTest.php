<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\TicketCategory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    private User $agent;

    private User $supervisor;

    private TicketCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->employee = User::where('email', 'employee@iki.co.id')->first();
        $this->agent = User::where('email', 'agent@iki.co.id')->first();
        $this->supervisor = User::where('email', 'supervisor@iki.co.id')->first();
        $this->category = TicketCategory::whereNotNull('parent_id')->first();
    }

    public function test_employee_can_browse_published_articles_only(): void
    {
        $response = $this->actingAs($this->employee)->get(route('knowledge-base.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Bagaimana kami dapat membantu Anda hari ini?');

        // See published article from seeder
        $response->assertSeeText('Panduan Koneksi Wi-Fi Internal PT IKI di Galangan & Kantor');

        // Draft article should not appear on employee page
        $response->assertDontSeeText('Draft Standarisasi Backup File Gambar Teknik Kapal (CAD)');
    }

    public function test_employee_cannot_view_draft_article_directly(): void
    {
        $draftArticle = Article::where('status', 'draft')->first();
        $this->assertNotNull($draftArticle);

        $response = $this->actingAs($this->employee)->get(route('knowledge-base.show', $draftArticle->slug));
        $response->assertStatus(404);
    }

    public function test_agent_can_view_draft_article_and_access_manage_page(): void
    {
        $draftArticle = Article::where('status', 'draft')->first();
        $this->assertNotNull($draftArticle);

        // Agent can see draft article
        $resShow = $this->actingAs($this->agent)->get(route('knowledge-base.show', $draftArticle->slug));
        $resShow->assertStatus(200);
        $resShow->assertSeeText($draftArticle->title);

        // Agent can access manage page
        $resManage = $this->actingAs($this->agent)->get(route('knowledge-base.manage'));
        $resManage->assertStatus(200);
        $resManage->assertSeeText('Manajemen Artikel & Solusi Mandiri');
    }

    public function test_regular_employee_cannot_access_manage_page(): void
    {
        $response = $this->actingAs($this->employee)->get(route('knowledge-base.manage'));
        $response->assertStatus(403);
    }

    public function test_agent_can_create_draft_article(): void
    {
        $response = $this->actingAs($this->agent)->post(route('knowledge-base.store'), [
            'title' => 'Cara Reset Kata Sandi Akun Email Perusahaan',
            'category_id' => $this->category->id,
            'content' => 'Langkah-langkah reset password email korporat...',
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('knowledge-base.manage'));

        $article = Article::where('title', 'Cara Reset Kata Sandi Akun Email Perusahaan')->first();
        $this->assertNotNull($article);
        $this->assertEquals('draft', $article->status);
        $this->assertEquals($this->agent->id, $article->author_id);
    }

    public function test_supervisor_can_publish_draft_article(): void
    {
        $draftArticle = Article::where('status', 'draft')->first();

        $response = $this->actingAs($this->supervisor)->post(route('knowledge-base.publish', $draftArticle));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $draftArticle->refresh();
        $this->assertEquals('published', $draftArticle->status);
        $this->assertNotNull($draftArticle->published_at);
    }

    public function test_user_can_submit_helpful_feedback_increments_counter(): void
    {
        $article = Article::where('status', 'published')->first();
        $initialHelpful = $article->helpful_count;

        $response = $this->actingAs($this->employee)->post(route('knowledge-base.helpful', $article));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $article->refresh();
        $this->assertEquals($initialHelpful + 1, $article->helpful_count);
    }

    public function test_searching_articles_filters_by_keyword(): void
    {
        $response = $this->actingAs($this->employee)->get(route('knowledge-base.index', [
            'search' => 'Paper Jam',
        ]));

        $response->assertStatus(200);
        $articles = $response->viewData('articles');
        $this->assertCount(1, $articles);
        $this->assertEquals('Penanganan Cepat Kertas Macet (Paper Jam) Printer Operasional', $articles->first()->title);
    }
}
