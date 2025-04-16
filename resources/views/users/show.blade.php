@extends('layouts.user-profile')

@section('subtitle', trans('user.profile'))

@section('user-content')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            @include('users.partials.profile')
        </div>
        <div class="col-md-8">
            @include('users.partials.parent-spouse')
            @include('users.partials.childs')
            @include('users.partials.siblings')
        </div>
    </div>
@endsection

@section ('ext_css')
<link rel="stylesheet" href="{{ asset('css/plugins/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/plugins/jquery.datetimepicker.css') }}">
@endsection

@section ('ext_js')
<script src="{{ asset('js/plugins/select2.min.js') }}"></script>
<script src="{{ asset('js/plugins/jquery.datetimepicker.js') }}"></script>
@endsection

@section ('script')
<script>
(function () {
    // $('select').select2();
    $('input[name=marriage_date]').datetimepicker({
        timepicker:false,
        format:'Y-m-d',
        closeOnDateSelect: true,
        scrollInput: false
    });
})();
</script>
<script>
$(document).ready(function() {
    $('.family-select').select2({
        placeholder: $(this).data('placeholder'),
        allowClear: true,
        ajax: {
            url: "{{ route('family-actions.search-family') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 10) < data.total_count
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 2
    });
    
    $('#create_new_family').change(function() {
        if($(this).is(':checked')) {
            $('#new_family_form').slideDown();
            $('select[name="family_id"]').prop('disabled', true);
        } else {
            $('#new_family_form').slideUp();
            $('select[name="family_id"]').prop('disabled', false);
        }
    });
    $('#create_new_parent_family').change(function() {
        if($(this).is(':checked')) {
            $('#new_parent_family_form').slideDown();
            $('select[name="parent_family_id"]').prop('disabled', true);
        } else {
            $('#new_parent_family_form').slideUp();
            $('select[name="parent_family_id"]').prop('disabled', false);
        }
    });
});
</script>
@endsection
