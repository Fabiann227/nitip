<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => ServiceCategory::query()->withCount('orders')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => null]);
    }

    public function store(ServiceCategoryRequest $request): RedirectResponse
    {
        ServiceCategory::query()->create($this->payload($request));

        return redirect()->route('admin.categories.index')->with('success', 'Kategori layanan ditambahkan.');
    }

    public function edit(ServiceCategory $category): View
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(ServiceCategoryRequest $request, ServiceCategory $category): RedirectResponse
    {
        $category->update($this->payload($request));

        return redirect()->route('admin.categories.index')->with('success', 'Kategori layanan diperbarui.');
    }

    public function destroy(ServiceCategory $category): RedirectResponse
    {
        if ($category->orders()->exists() || $category->trips()->exists()) {
            $category->update(['is_active' => false]);

            return back()->with('success', 'Kategori sudah dipakai pesanan, jadi dinonaktifkan alih-alih dihapus.');
        }

        $category->delete();

        return back()->with('success', 'Kategori dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ServiceCategoryRequest $request): array
    {
        $data = $request->validated();

        return $data + [
            'requires_document' => $request->boolean('requires_document'),
            'has_item_cost' => $request->boolean('has_item_cost'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
