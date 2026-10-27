<div>
    @if(is_callable($value))
        {{ $value($getRecord()) }}
    @else
        {{ $value }}
    @endif
</div>
