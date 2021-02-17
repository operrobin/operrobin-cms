@extends('template.layout')

@section('js_before')
    <script type="text/javascript" src="{{ asset('template/js/custom/infinity-scrolling.js') }}"></script> 
    <script type="text/javascript">
        //API Hit Setting
        var page = 1;
        var perPage = 10;
        var endOfRequest = false;

        //API Hit Setting
        var log_page = 1;
        var log_eor = false;
    </script>
@endsection

@section('css_before')

@endsection

@section('css_after')

@endsection

@section('title', 'WA Marketing Blast - Promo Blast')
    
@section('content')
    <div class="block block-rounded block-bordered">
        <div class="block-content">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <select id="type" class="btn btn-primary">
                            <option value="promo_title">Promo Title</option>
                        </select>
                    </div>
                    <input type="text" class="form-control" id="search" placeholder="Search">
                    <div class="input-group-append mr-2">
                        <button type="button" class="btn btn-primary" onclick="search()">
                            <i class="fa fa-search mr-1"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#create-modal">
                        <i class="fa far-fw fa-plus mr-1"></i> Create WA Promo
                    </button>
                </div>
            </div>

            @if (Session::has('success'))
                <div class="alert alert-success alert-dismissable" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <p class="mb-0">{{Session::get('success')}}</p>
                </div>
            @elseif (Session::has('error'))
                <div class="alert alert-danger alert-dismissable" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <p class="mb-0">{{Session::get('error')}}</p>
                </div>
            @endif

            <div id="wrapper" style="height: 300px; overflow-y: scroll;" class="table-responsive">
                <table id="allowance-table" class="table table-bordered table-striped table-vcenter display nowrap ">
                    <thead class="text-center">
                        <th>Promo ID</th>
                        <th>Promo Title</th>
                        <th>Promo Log</th>
                        <th>Action</th>
                    </thead>
                    <tbody id="content" class="text-center">

                    </tbody>
                </table>
            </div>
            <br>
        </div>
    </div>
@endsection

@section('modal')
    @include('features.wa-marketing-blast.promo-blast.function.modal')
@endsection

@section('js_after')
    <script type='text/javascript'>

        //global
        let searchValue = "";
        let filter = "";

        $(document).ready(function() {
            loadTable();
        });

        function loadTable(){
            page = 1;
            endOfRequest = false;

            $('#content').empty();

            loaderOn();

            $.ajax({
                "type": "GET",
                "url": "/wa-marketing-blast/promo-blast/pagination",
                "data": {
                    page: page,
                    size: perPage,
                    key: filter,
                    value: searchValue
                },
                "dataType": "html",
                success: function(e) {
                    $('#content').append(e);
                    loaderOff();
                },
                error: function(e){
                    alert("Connection to system is error! Please contact system administrator!");
                    loaderOff();
                }
            });
        }

        infinityScroll("wrapper", 
            "content", 
            "GET", 
            "/wa-marketing-blast/promo-blast/pagination", 
            "html",
            {
                "page": page+1,
                "size": perPage,
                "key": filter,
                "value": searchValue
            },
            function(){
                if(!$('#end-of-content').length){
                    loaderOn();
                }
            },
            function(){
                page = page+1;
            },
            function(){
                return {
                    "param": {
                        "page": page+1,
                        "size": perPage
                    },
                    "endOfRequest": endOfRequest
                }
            },
            function(success){
                if(!$('#end-of-content').length){
                    loaderOff();

                    $('#content').append(success);
                }
            },
            function(xhr, status, error){
                loaderOff();

                if(xhr.status === 404){
                    customSwal(xhr.status+ " - " + xhr.statusText, 'Requested URL is not found!');
                }else{
                    customSwal(xhr.status+ " - " + xhr.statusText, "Connection to system is error! Please contact system administrator!");
                }
            },
            endOfRequest
        );

        function search() {
            filter = $("#type").val();
            if(filter == "role_id") {
                searchValue = $("#search-select").val();
            } else {
                searchValue = $("#search").val();
            }

            loadTable();
        }

        function detailView(id) {
            loaderOn();
            $.ajax({
                url: '/wa-marketing-blast/promo-blast/' + id,
                method: 'GET',
                dataType: 'json',
                success: function(rspn) {
                    $('#view-form').find('[name="id"]').val(rspn.id);
                    $('#view-form').find('[name="title"]').val(rspn.promo_title);
                    $('#view-form').find('[name="content"]').val(rspn.promo_content);

                    loaderOff();
                    $('#view-modal').modal('show');

                    tableLogs();
                },
                error: function(err) {
                    loaderOff();
                },
            });
        }

        function tableLogs() {
            log_page = 1;
            log_eor = false;

            $('#log_content').empty();

            $.ajax({
                "type": "GET",
                "url": "/wa-marketing-blast/promo-blast/logs",
                "data": {
                    "page": 1,
                    "size": 5,
                    "key": "master_promo_id",
                    "value": $('#view-form').find('[name="id"]').val()
                },
                "dataType": "html",
                "success": function(e) {
                    $('#log_content').append(e);
                    loaderOff();
                },
                "error": function(e){
                    alert("Connection to system is error! Please contact system administrator!");
                    loaderOff();
                }
            });

            infinityScroll("log_wrapper", 
                "log_content", 
                "GET", 
                "/wa-marketing-blast/promo-blast/logs", 
                "html",
                {
                    "page": log_page+1,
                    "size": 5,
                    "key": "master_promo_id",
                    "value": $('#view-form').find('[name="id"]').val()
                },
                function(){
                    if(!$('#eoc').length){
                        loaderOn();
                    }
                },
                function(){
                    log_page = log_page+1;
                },
                function(){
                    return {
                        "param": {
                            "page": log_page+1,
                            "size": 5,
                            "key": "master_promo_id",
                            "value": $('#view-form').find('[name="id"]').val()
                        },
                        "endOfRequest": log_eor
                    }
                },
                function(success){
                    if(!$('#eoc').length){
                        loaderOff();

                        $('#log_content').append(success);
                    }
                },
                function(xhr, status, error){
                    loaderOff();

                    alert("Connection to system is error! Please contact system administrator!");
                },
                log_eor
            );
        }

        function updateView(id) {
            loaderOn();
            $.ajax({
                url: '/wa-marketing-blast/promo-blast/' + id,
                method: 'GET',
                dataType: 'json',
                success: function(rspn) {
                    $('#update-form').find('[name="id"]').val(rspn.id);
                    $('#update-form').find('[name="title"]').val(rspn.promo_title);
                    $('#update-form').find('[name="content"]').val(rspn.promo_content);

                    loaderOff();
                    $('#update-modal').modal('show');
                },
                error: function(err) {
                    loaderOff();
                },
            });
        }

        function deleteView(id, name) {
            $('#delete-form').find('[name="id"]').val(id);
            $('#delete-name').text(name);
            $('#delete-modal').modal('show');
        }

    </script>
@endsection