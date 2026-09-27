@extends('layouts.app')
@section('title','Yêu thích')
@section('content')<div class="container shop-shell py-5"><h1 class="section-title mb-4">Sản phẩm yêu thích</h1><div class="row g-4">@forelse($items as $item)<div class="col-6 col-lg-3"><x-product-card :product="$item->product"/><form method="POST" action="{{ route('wishlist.destroy',$item->product) }}">@csrf @method('DELETE')<button class="btn btn-link text-danger px-0">Xóa khỏi yêu thích</button></form></div>@empty<p>Danh sách yêu thích đang trống.</p>@endforelse</div>{{ $items->links() }}</div>@endsection
