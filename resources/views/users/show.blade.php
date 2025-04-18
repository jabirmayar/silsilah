@extends('layouts.user-profile')

@section('subtitle', trans('user.profile'))

@section('user-content')

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
    // Add this to your JavaScript file or within a <script> tag in your blade template
$(document).ready(function() {
    // Generic function to initialize Select2 for person search
    $('.person-search').each(function() {
        var $select = $(this);
        var gender = $select.data('gender') || null;
        
        $select.select2({
            ajax: {
                url: "{{ route('family-actions.search-people') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page,
                        gender: gender
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
            placeholder: $(this).data('placeholder'),
            minimumInputLength: 2,
            allowClear: true
        });
    });
    
    $('.person-search').on('change', function() {
        var formId = $(this).closest('form').attr('id');
        if ($(this).val()) {
            $('#' + formId + ' input[type="text"]').val('');
        }
    });
});
</script>
<script>
$(document).ready(function() {
    $('.couple-search').select2({
        ajax: {
            url: "{{ route('family-actions.search-couples') }}",
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
        placeholder: $(this).data('placeholder'),
        minimumInputLength: 2,
        allowClear: true
    });
});
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
   
   var initialFamilyId = $('select[name="family_id"]').val();
   if (initialFamilyId) {
       $.ajax({
           url: "{{ route('family-actions.get-sub-families') }}",
           data: { family_id: initialFamilyId },
           dataType: 'json',
           success: function(data) {
               var subFamilySelect = $('select[name="sub_family_id"]');
               var currentSubFamilyId = subFamilySelect.val();
               
               subFamilySelect.empty();
               subFamilySelect.append('<option value="">{{ __("app.select_sub_family") }}</option>');
               
               $.each(data, function(index, subFamily) {
                   var selected = (subFamily.id == currentSubFamilyId) ? 'selected' : '';
                   subFamilySelect.append('<option value="' + subFamily.id + '" ' + selected + '>' + subFamily.name + '</option>');
               });
           }
       });
   }
   
   $('select[name="family_id"]').on('change', function() {
       var familyId = $(this).val();
       if (familyId) {
           $.ajax({
               url: "{{ route('family-actions.get-sub-families') }}",
               data: { family_id: familyId },
               dataType: 'json',
               success: function(data) {
                   var subFamilySelect = $('select[name="sub_family_id"]');
                   subFamilySelect.empty();
                   subFamilySelect.append('<option value="">{{ __("app.select_sub_family") }}</option>');
                   
                   $.each(data, function(index, subFamily) {
                       subFamilySelect.append('<option value="' + subFamily.id + '">' + subFamily.name + '</option>');
                   });
               }
           });
       } else {
           $('select[name="sub_family_id"]').empty();
       }
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
   
   $('#create_new_sub_family').change(function() {
       if($(this).is(':checked')) {
           $('#new_sub_family_form').slideDown();
           $('select[name="sub_family_id"]').prop('disabled', true);
       } else {
           $('#new_sub_family_form').slideUp();
           $('select[name="sub_family_id"]').prop('disabled', false);
       }
   });
});
</script>
@endsection
