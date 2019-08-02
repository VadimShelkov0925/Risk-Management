@if(count($files))
    @foreach($files as $file)
        <div class="item">
            <input type='checkbox' class='item-check' data-id="{{ $file->id }}">
            <div class="image">
                <img src="{{url('/')}}/uploads/frontend/{{ $file->image }}" alt="{{ $file->name }}">
            </div>

            <div class="content">
                <div class="name">{{ $file->name }}</div>
                <div class="type">{{ !empty($file->type->name) ? $file->type->name :pathinfo($file->filename, PATHINFO_EXTENSION) }}</div>
                <div class="size">{{ $file->size }}</div>
            </div>

            <div class="optionBlock">
                <div data-id="{{ $file->id }}" title="Print" class="print" onclick="sf_print(this)"></div>
                <div title="Download" data-url="{{url('/')}}/uploads/frontend/{{ $file->filename }}" class="download"></div>
                <div title="Delete" data-id="{{ $file->id }}" class="delete" onclick="sf_delete(this)"></div>
                <div data-url="{{url('/')}}/uploads/frontend/{{ $file->filename }}" title="Share" class="share" onclick="sf_share(this)"></div>
            </div>
        </div>
    @endforeach
@endif