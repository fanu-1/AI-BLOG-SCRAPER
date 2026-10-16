@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="topbar" style="margin-bottom:16px;">
    <div>
        <h1 style="margin:0;">Categories</h1>
        <div class="muted">Organize the site into clean editorial buckets.</div>
    </div>
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Back</a>
</div>

<div class="panel" style="margin-bottom:20px;">
    <h2 style="margin-top:0;">New Category</h2>
    <form method="POST" action="{{ route('admin.categories.store') }}" class="form-grid">
        @csrf
        <label>Name <input name="name" required placeholder="Technology"></label>
        <label>Sort order <input type="number" name="sort_order" value="0"></label>
        <label class="full-width">Description <textarea name="description" placeholder="Short category description"></textarea></label>
        <label><span>Active</span><input type="checkbox" name="is_active" value="1" checked></label>
        <div class="full-width"><button class="btn btn-primary" type="submit">Create Category</button></div>
    </form>
</div>

<div class="table-box panel">
    <table>
        <thead>
            <tr><th>Name</th><th>Slug</th><th>Sort</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td colspan="5">
                        <form id="category-update-{{ $category->id }}" method="POST" action="{{ route('admin.categories.update', $category) }}" class="form-grid">
                            @csrf
                            @method('PATCH')
                            <label>Name <input name="name" value="{{ $category->name }}"></label>
                            <label>Sort order <input type="number" name="sort_order" value="{{ $category->sort_order }}"></label>
                            <label class="full-width">Description <textarea name="description">{{ $category->description }}</textarea></label>
                            <label><span>Active</span><input type="checkbox" name="is_active" value="1" @checked($category->is_active)></label>
                        </form>
                        <div class="actions" style="margin-top:12px;">
                            <button class="btn btn-primary btn-small" type="submit" form="category-update-{{ $category->id }}">Save</button>
                            <form id="category-delete-{{ $category->id }}" method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete category?')">
                                @csrf
                                @method('DELETE')
                            </form>
                            <button class="btn btn-danger btn-small" type="submit" form="category-delete-{{ $category->id }}">Delete</button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
