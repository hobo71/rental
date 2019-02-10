<div class="d-flex justify-content-between align-items-center">
  <a href="/product/category/{{ $category->id }}" class="list-group-item list-group-item-action {{ $category->inactive ? 'list-group-item-danger' : 'list-group-item-light' }}">
    @if ($category->inactive)
      <del>{{ $category->name }}</del>
    @else
      {{ $category->name }}
    @endif
  </a>

  @if ($loggedIn)
    <a href="/product/category/{{ $category->id }}/edit" class="btn btn-primary btn-sm">EDIT</a>
  @endif

</div>

@if ($category->children()->count() > 0)

  @if ($loggedIn)
    <?php $categories = $category->children; ?>
  @else
    <?php $categories = $category->activeChildren; ?>
  @endif

  @foreach($categories as $category)
    <div class="list-group list-group-flush ml-3">
      @include('product.category.categories', [
        'category' => $category
      ])
    </div>
  @endforeach

@endif
