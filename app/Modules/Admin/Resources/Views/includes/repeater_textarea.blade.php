
<div  class="repeater{{$key}}">
    <h3 class="ml-5">{{ $labelName }}</h3>
    <div data-repeater-list="{{ $baseKeyName }}">
        <div data-repeater-item>

            <div class="form-group">

                @foreach($fields as $field)

                    <label class="{{ $field['labelCol'] }} control-label" for="name">{{ $field['labelName'] }}  <span class="text-danger">*</span>:</label>
                    <div class="{{ $field['inputCol'] }}">
                        @include('admin.includes.textarea', ['name' => $field['name'] ])
                    </div>
                    <div class="{{ $field['deleteCol'] }}">
                        <input data-repeater-delete type="button" class="btn btn-sm btn-danger pull-right" value="@lang('admin.repeater_delete')">
                    </div>

                @endforeach
            </div>

        </div>
    </div>
    <div class="col-md-12">
        <input data-repeater-create type="button" class="btn btn-sm btn-success addForm" value="@lang('admin.repeater_add')">
    </div>
</div>

@include('admin.includes.error-span', ['errors' => $errors,'name'  => $key])

{{--@section('scripts')--}}

<script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>

<script>
    // $(document).ready(function () {

    $('.addForm').on('click', function(){
        setTimeout(function(){
            CKEDITOR.replaceAll('ckeditor');

        },200)
    });

    var $repeater = $('.repeater{{$key}}').repeater({

        // (Optional)
        // start with an empty list of repeaters. Set your first (and only)
        // "data-repeater-item" with style="display:none;" and pass the
        // following configuration flag
        initEmpty: false,
        // (Optional)
        // "defaultValues" sets the values of added items.  The keys of
        // defaultValues refer to the value of the input's name attribute.
        // If a default value is not specified for an input, then it will
        // have its value cleared.
        defaultValues: {
            'text-input': ''
        },
        // (Optional)
        // "show" is called just after an item is added.  The item is hidden
        // at this point.  If a show callback is not given the item will
        // have $(this).show() called on it.
        show: function () {
            $(this).slideDown();
        },
        create: function(){
            console.log('dsa');
        },
        // (Optional)
        // "hide" is called when a user clicks on a data-repeater-delete
        // element.  The item is still visible.  "hide" is passed a function
        // as its first argument which will properly remove the item.
        // "hide" allows for a confirmation step, to send a delete request
        // to the server, etc.  If a hide callback is not given the item
        // will be deleted.
        hide: function (deleteElement) {
            if(confirm('Are you sure you want to delete this element?')) {
                $(this).slideUp(deleteElement);
            }
        },
        // (Optional)
        // You can use this if you need to manually re-index the list
        // for example if you are using a drag and drop library to reorder
        // list items.
        ready: function (setIndexes) {
            // $dragAndDrop.on('drop', setIndexes);
        },
        // (Optional)<hr>
        // Removes the delete button from the first list item,
        // defaults to false.
        isFirstItemUndeletable: true
    })

    @php $allFieldsValue = []; @endphp
    @if(!empty($oldValues))
        @foreach($oldValues     as $key => $oldValue)
            @foreach($fields as $field)
                @php $allFieldsValue[$key][$field['name']] = array_key_exists($field['name'], $oldValue) ? $oldValue[$field['name']] : '' @endphp
            @endforeach
        @endforeach
    @endif


    @if($allFieldsValue)
    $repeater.setList(
            {!! json_encode($allFieldsValue, JSON_UNESCAPED_UNICODE) !!}
    )
    @endif

    // });
</script>

{{--@endsection--}}