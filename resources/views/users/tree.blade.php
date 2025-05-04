@extends('layouts.user-profile-wide')

@section('subtitle', trans('app.family_tree'))

@section('user-content')
    <div id="tree" style="width: 100%; height: 600px;"></div>
@endsection

@section('ext_css')
    <link rel="stylesheet" href="{{ asset('css/tree.css') }}">
@endsection

@section('ext_js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rawTree = @json($treeData);

        const members = [];

        function flattenFamilyTree(person, parentId = null) {
            const node = {
                id: person.id,
                name: `<a href="{{ url('users') }}/${person.id}/tree">${person.name}</a>`,
                photo: person.photo,
                title: person.title,
                siblingIds: person.siblingIds || [],
                childIds: person.childIds || [],
                userProfileUrl: `{{ url('users') }}/${person.id}/tree`,
                fatherId: person.father_id || null,
                motherId: person.mother_id || null,
                spouseIds: person.gender_id == 2 && person.husbands 
                                ? person.husbands.map(husband => husband.id) : person.gender_id == 1 && person.wifes 
                                ? person.wifes.map(wife => wife.id) : [],
            };

            if (parentId) {
                node.fatherId = parentId;
            }

            members.push(node);

            if (person.children) {
                person.children.forEach(child => {
                    flattenFamilyTree(child, person.id);
                });
            }
        }

        flattenFamilyTree(rawTree);

        const familyTree = new FamilyTree2(document.getElementById('tree'));
        familyTree.templateName = 'mars';
        let template = familyTree.template;

        familyTree.onNodeClick(function (args) {
            if (this.readOnly) {
                this.centerNodes([args.node]);
            }

            if (args.node.userProfileUrl) {
                window.location.href = args.node.userProfileUrl;
            }
        });

        familyTree.onSvgClick(function () {
            this.fit();
        });

        familyTree.addFamilyMembers(members).draw(rawTree.id);

    });
</script>

@endsection
