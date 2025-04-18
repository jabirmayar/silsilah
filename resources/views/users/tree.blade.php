@extends('layouts.user-profile-wide')

@section('subtitle', trans('app.family_tree'))

@section('user-content')


<button id="reset-chart" class="btn btn-sm btn-primary mb-2">Reset View</button>
<div id="chart-container"></div>

@endsection

@section('ext_css')
<link rel="stylesheet" href="{{ asset('css/tree.css') }}">
@endsection

@section('ext_js')
<script>
$(function () {
    const treeData = @json($treeData);

    const chart = $('#chart-container').orgchart({
        data: treeData,
        nodeContent: 'title',
        nodeID: 'id',
        visibleLevel: 6,
        pan: true,
        zoom: true,
        createNode: function ($node, data) {
            const imageSrc = data.photo || "{{ asset('images/icon_user_1.png') }}";
            const profileUrl = `{{ url('users/') }}/${data.id}/tree`;

            const customContent = `
                <div class="custom-node">
                    <img class="avatar" src="${imageSrc}" />
                    <div class="name">
                        <a href="${profileUrl}" class="name-link">
                            ${data.name}
                        </a>
                    </div>
                    <div class="title">${data.title}</div>
                </div>
            `;

            $node.append(customContent);
            $node.children('.content').remove();
            $node.children('.title').remove();
        }
    });

    $('#chart-container .orgchart').css('transform', 'scale(0.8)');
});
</script>    
<script>
    $('#reset-chart').on('click', function () {
    $('#chart-container .orgchart').css({
        transform: 'scale(0.8) translate(0px, 0px)',
        transformOrigin: '0 0'
    });
});

</script>
@endsection


