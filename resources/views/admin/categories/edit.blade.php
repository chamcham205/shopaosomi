@extends('layouts.admin')
@section('title', 'Sửa Danh mục')
@section('page-title', 'Sửa Danh mục')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i></a>
    <h5 class="mb-0 fw-700">Sửa: {{ $category->name }}</h5>
</div>
@if($errors->any())
<div class="alert alert-danger mb-3"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $category->name) }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
            </div>
            @if($category->image)
            <div class="mb-2">
                <img src="{{ asset('storage/' . $category->image) }}" class="rounded" height="80" alt="Ảnh hiện tại">
            </div>
            @endif
            <div class="mb-3">
                <label class="form-label">Thay ảnh mới</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $category->status) === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ old('status', $category->status) === 'inactive' ? 'selected' : '' }}>Ẩn</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Thứ tự</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order) }}" min="0">
                </div>
            </div>
            <button type="submit" class="btn btn-accent w-100"><i class="fa-solid fa-floppy-disk me-2"></i>Lưu thay đổi</button>
        </form>
    </div>
</div>
@endsection
