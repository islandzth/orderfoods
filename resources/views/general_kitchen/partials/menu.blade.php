<div class="row menu-kitchen">
    <div class="col-4">
        <div class="title-kitchen">Chờ chế biến</div>
    </div>
    <div class="col-8 text-right">
        <div class="row" style="float: right;">
            <a class="btn {{ $current === 'orders' ? 'btn-info' : 'btn-secondary' }}  btn-sm" href="{{ route('general-kitchen.by-order') }}">Ưu tiên</a>
            <a class="btn {{ $current === 'foods' ? 'btn-info' : 'btn-secondary' }}  btn-sm" href="{{ route('general-kitchen.by-food') }}">Theo món</a>
            <a class="btn {{ $current === 'boards' ? 'btn-info' : 'btn-secondary' }} btn-sm" href="{{ route('general-kitchen.by-board') }}">Theo bàn</a>
        </div>

    </div>
</div>
