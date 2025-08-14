@extends('admin::layouts.admin')

@section('content')
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
                <h1 class="flex-sm-fill font-size-h2 font-w400 mt-2 mb-0 mb-sm-2">{{ $news->title }}</h1>
                <nav class="flex-sm-00-auto ml-sm-3" aria-label="breadcrumb">
                    <a href="{{ route('developer.news.index') }}" class="btn btn-light">
                        <i class="fa fa-arrow-left mr-1"></i> Back to News
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="block">
            <div class="block-content">
                <div class="row">
                    <div class="col-lg-8 offset-lg-2">
                        @if($news->thumbnail)
                            <div class="mb-4">
                                <img src="{{ $news->thumbnail }}" 
                                     alt="{{ $news->title }}" 
                                     class="img-fluid w-100 rounded" 
                                     style="max-height: 400px; object-fit: cover;">
                            </div>
                        @endif

                        <div class="mb-4">
                            <h1 class="font-w600 mb-3">{{ $news->title }}</h1>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                                <div>
                                    @if($news->created_by_name)
                                        <small class="text-muted">
                                            <i class="fa fa-user mr-1"></i>
                                            By: {{ $news->created_by_name }}
                                        </small>
                                    @endif
                                </div>
                                <div>
                                    @if($news->published_at)
                                        <small class="text-muted">
                                            <i class="fa fa-calendar mr-1"></i>
                                            {{ $news->published_at->format('F d, Y') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="content-news">
                            {!! $news->content !!}
                        </div>

                        @if($news->images && $news->images->count() > 1)
                            <div class="mt-4">
                                <h4 class="font-w600 mb-3">Gallery</h4>
                                <div class="row">
                                    @foreach($news->images->skip(1) as $image)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="img-link img-link-zoom-in">
                                                <img src="{{ $image->image }}" 
                                                     alt="{{ $image->name }}" 
                                                     class="img-fluid rounded" 
                                                     style="height: 200px; width: 100%; object-fit: cover;">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.content-news {
    font-size: 1.1rem;
    line-height: 1.7;
}

.content-news p {
    margin-bottom: 1.5rem;
}

.content-news img {
    max-width: 100%;
    height: auto;
    border-radius: 0.375rem;
    margin: 1rem 0;
}

.img-link:hover img {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}
</style>
@endpush
