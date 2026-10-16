<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\TicketCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KnowledgeBaseController extends Controller
{
    /**
     * Public / Employee Knowledge Base browser (Self-Service).
     */
    public function index(Request $request)
    {
        $query = Article::with(['category', 'author'])
            ->published();

        // Keyword search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $articles = $query->orderBy('views_count', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Top categories with published article counts
        $categories = TicketCategory::whereHas('articles', function ($q) {
            $q->where('status', 'published');
        })->withCount(['articles' => function ($q) {
            $q->where('status', 'published');
        }])->get();

        // Popular helpful articles
        $popularArticles = Article::published()
            ->orderBy('helpful_count', 'desc')
            ->take(5)
            ->get();

        return view('knowledge_base.index', compact(
            'articles',
            'categories',
            'popularArticles'
        ));
    }

    /**
     * Display a specific published article.
     */
    public function show(Request $request, string $slug)
    {
        $article = Article::with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        // If article is not published, only authorized agents/supervisors can view
        if (! $article->isPublished() && ! $request->user()->hasPermission('knowledge.manage')) {
            abort(404);
        }

        // Increment view count
        $article->recordView();

        // Related articles in the same category
        $relatedArticles = Article::published()
            ->where('id', '!=', $article->id)
            ->where('category_id', $article->category_id)
            ->take(4)
            ->get();

        return view('knowledge_base.show', compact('article', 'relatedArticles'));
    }

    /**
     * Increment helpful count for an article.
     */
    public function helpful(Request $request, Article $article)
    {
        $article->markAsHelpful();

        return back()->with('success', 'Terima kasih atas masukan Anda! Tanggapan ini membantu kami meningkatkan kualitas dokumentasi TI.');
    }

    /**
     * Management view for IT Agents and Supervisors to curate articles.
     */
    public function manage(Request $request)
    {
        if (! $request->user()->hasPermission('knowledge.manage')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola Basis Pengetahuan.');
        }

        $query = Article::with(['category', 'author']);

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $articles = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $categories = TicketCategory::all();

        $stats = [
            'total' => Article::count(),
            'published' => Article::where('status', 'published')->count(),
            'draft' => Article::where('status', 'draft')->count(),
            'total_views' => Article::sum('views_count'),
        ];

        return view('knowledge_base.manage', compact('articles', 'categories', 'stats'));
    }

    /**
     * Show form to create a new article.
     */
    public function create(Request $request)
    {
        if (! $request->user()->hasPermission('knowledge.manage')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat artikel.');
        }

        $categories = TicketCategory::all();

        return view('knowledge_base.create', compact('categories'));
    }

    /**
     * Store new article.
     */
    public function store(Request $request)
    {
        if (! $request->user()->hasPermission('knowledge.manage')) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat artikel.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:ticket_categories,id'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
        ]);

        $publishedAt = null;
        if ($validated['status'] === 'published') {
            $publishedAt = Carbon::now('UTC');
        }

        $article = Article::create([
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'author_id' => $request->user()->id,
            'content' => $validated['content'],
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()->route('knowledge-base.manage')
            ->with('success', "Artikel panduan [{$article->title}] berhasil disimpan.");
    }

    /**
     * Show form to edit an article.
     */
    public function edit(Request $request, Article $article)
    {
        if (! $request->user()->hasPermission('knowledge.manage')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah artikel.');
        }

        $categories = TicketCategory::all();

        return view('knowledge_base.edit', compact('article', 'categories'));
    }

    /**
     * Update an article.
     */
    public function update(Request $request, Article $article)
    {
        if (! $request->user()->hasPermission('knowledge.manage')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah artikel.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:ticket_categories,id'],
            'content' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
        ]);

        $publishedAt = $article->published_at;
        if ($validated['status'] === 'published' && ! $publishedAt) {
            $publishedAt = Carbon::now('UTC');
        }

        $article->update([
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'content' => $validated['content'],
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ]);

        return redirect()->route('knowledge-base.manage')
            ->with('success', "Artikel [{$article->title}] berhasil diperbarui.");
    }

    /**
     * Publish a draft article directly.
     */
    public function publish(Request $request, Article $article)
    {
        if (! $request->user()->hasPermission('knowledge.manage')) {
            abort(403, 'Anda tidak memiliki hak akses untuk menerbitkan artikel.');
        }

        $article->update([
            'status' => 'published',
            'published_at' => Carbon::now('UTC'),
        ]);

        return back()->with('success', "Artikel [{$article->title}] telah berhasil diterbitkan dan dapat diakses pengguna.");
    }

    /**
     * Remove an article.
     */
    public function destroy(Request $request, Article $article)
    {
        if (! $request->user()->hasPermission('knowledge.manage')) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus artikel.');
        }

        $title = $article->title;
        $article->delete();

        return redirect()->route('knowledge-base.manage')
            ->with('success', "Artikel [{$title}] berhasil dihapus.");
    }
}
