<div class="alert">
    @if(isset($title))
        <h3>{{ $title }}</h3>
    @endif
    {{ $slot }}
</div>