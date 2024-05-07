<label class="col-md-3 control-label" for="{{$selectName}}">{{ $labelName }} </label>
<div class="col-md-9">
    @if($ajaxSearch)
        <select id="{{$selectName}}" name="{{$name}}" class="select-select2-{{$selectName}}" @if($multiple) multiple @endif>
            @if($oldValues && $multiple)
                @foreach($oldValues as $oldVl)
                    <option value="{{ $oldVl->id }}" selected > {{ $oldVl->{$optionName} }}</option>
                @endforeach
            @elseif($oldValues)
                <option value="{{ $oldValues->id }}" selected > {{ $oldValues->{$optionName} }}</option>
            @endif
        </select>
    @else
        <select name="{{$name}}" class="select-select2" @if($multiple) multiple @endif>
            @foreach($optionData as $dt)
                <option value="{{ $dt->id }}" @if(in_array($dt->id, $oldValues)) selected @endif> {{ $dt->{$optionName} }}</option>
            @endforeach
        </select>
    @endif
    @include('admin::includes.error-span', ['errors' => $errors,'name'  => $selectName])
</div>


@if($ajaxSearch)

    <script>

        $('.select-select2-{{$selectName}}').select2({
            minimumInputLength: 2,
            ajax: {
                url: "{{ $searchRoute }}",
                dataType: 'json',
                delay: 250,
                data: function (term) { return term; },
                processResults: function (data) {

                    var isOtherNames = false;
                    if ( this.ajaxOptions.url.includes('persons') || this.ajaxOptions.url.includes('places') ) {
                        isOtherNames = true;
                    }

                    $.each(data, function (k, item) {

                        if (!isOtherNames) {
                            item.text = item.{{{$optionName}}};
                        } else {
                            var text = '';
                            if (item.other_names) {
                                item.other_names.forEach((element) => {
                                    text = text + element.text + ',';
                                })
                                item.text = item.name + ' - ' + text;
                            } else {
                                item.text = item.name;
                            }
                        }
                    });
                    return { results: data };
                }
            }
        });

</script>

@endif
