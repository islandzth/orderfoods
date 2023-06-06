<div class="col-12 col-sm-12 mb-2" id="group_board_{{ $board->id }}">
    <div class="card">
        <button class="btn btn-info text-left pt-3 pb-3" data-toggle="collapse" data-target="#heading_{{ $board->id }}" aria-expanded="true" aria-controls="heading_{{ $board->id }}">
            #{{ $board->name }}
        </button>
        <div class="collapse show" id="heading_{{ $board->id }}">

            <div class="card card-body">

                <ul class="list-group">
                </ul>
            </div>
        </div>
    </div>
</div>
