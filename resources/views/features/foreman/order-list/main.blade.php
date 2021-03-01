@extends('template.layout')

@section('js_before')
    <script type="text/javascript" src="{{ asset('template/js/custom/infinity-scrolling.js') }}"></script> 
    <script type="text/javascript">
        //API Hit Setting
        var page = 1;
        var perPage = 10;
        var endOfRequest = false;
    </script>
    @if (Request::get("page") == 'confirmed')
        <script type="text/javascript">
            let url_with_param = "/foreman/order-list/pagination-confirmed";
        </script>
    @else
        <script type="text/javascript">
            let url_with_param = "/foreman/order-list/pagination-pending";
        </script>
    @endif
@endsection

@section('css_before')

@endsection

@section('css_after')

@endsection

@section('title', 'Foreman - Order List')
    
@section('content')
    <div class="block block-bordered js-classic-nav d-none d-sm-block">
        <div class="block-content block-content-full">
            <div class="row no-gutters border">
                <div class="col-sm-6 col-xl-6 invisible" data-toggle="appear">
                    <a class="block block-bordered block-link-pop text-center mb-0 @if (Request::get('page') != 'confirmed') bg-gray-lighter @endif" href="/foreman/order-list" onclick="loaderOn()">
                        <div class="block-content block-content-full text-center">
                            <i class="far fa-hourglass fa-2x text-primary d-none d-sm-inline-block mb-3"></i>
                            <div class="font-w600 text-uppercase">Foreman Pending</div>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6 col-xl-6 invisible" data-toggle="appear">
                    <a class="block block-bordered block-link-pop text-center mb-0 @if (Request::get('page') == 'confirmed') bg-gray-lighter @endif" href="/foreman/order-list?page=confirmed" onclick="loaderOn()">
                        <div class="block-content block-content-full text-center">
                            <i class="far fa-check-circle fa-2x text-primary d-none d-sm-inline-block mb-3"></i>
                            <div class="font-w600 text-uppercase">Foreman Confirmed</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="block block-rounded block-bordered">
        <div class="block-content">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <select id="type" class="btn btn-primary">
                            <option value="booking_no">Booking Number</option>
                            <option value="customer_name">Customer Name</option>
                        </select>
                    </div>
                    <input type="text" class="form-control" id="search" placeholder="Search">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" onclick="search()">
                            <i class="fa fa-search mr-1"></i>
                        </button>
                    </div>
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
                        <th>Booking Number</th>
                        <th>Customer Name</th>
                        <th>Bengkel Name</th>
                        <th>Order Type</th>
                        <th>Order Status</th>
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
    @include('features.foreman.order-list.function.modal')
@endsection

@section('js_after')
    <script type="text/javascript" src="{{ asset('template/js/plugins/jquery.maskedinput/jquery.maskedinput.min.js') }}"></script> 

    <script type='text/javascript'>

        //global
        let searchValue = "";
        let filter = "";

        $(function() {
            Dashmix.helpers(['masked-inputs']);
        });

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
                "url": url_with_param,
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
            url_with_param, 
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
                url: '/foreman/order-list/' + id,
                method: 'GET',
                dataType: 'json',
                success: function(rspn) {
                    $('#view-form').find('[name="cName"]').val(rspn.customer_name);
                    $('#view-form').find('[name="cHp"]').val(rspn.customer_hp);
                    $('#view-form').find('[name="cEmail"]').val(rspn.customer_email);
                    $('#view-form').find('[name="cAddress"]').val(rspn.customer_address);
                    $('#view-form').find('[name="vBrand"]').val(rspn.vehicle_brand.brand_name);
                    $('#view-form').find('[name="vType"]').val(rspn.vehicle_type.vehicle_name);
                    $('#view-form').find('[name="vPlat"]').val(rspn.vehicle_plat);
                    $('#view-form').find('[name="bName"]').val(rspn.workshop_bengkel.bengkel_name);
                    $('#view-form').find('[name="pkbNomer"]').val(rspn.pkb_nomer);
                    $('#view-form').find('[name="pkbEstimation"]').val(rspn.pkb_estimation);
                    $('#view-form').find('[name="bNumber"]').val(rspn.booking_no);
                    $('#view-form').find('[name="bTime"]').val(rspn.booking_time);

                    if(rspn.pkb_file == null || rspn.pkb_file == "") {
                        $('#download-file').prop('href', 'javascript:void(0);');
                        $('#download-file button').prop('disabled', true);
                    } else {
                        $('#download-file').prop('href', rspn.pkb_file);
                        $('#download-file button').prop('disabled', false);
                    }

                    switch (rspn.order_type) {
                        case 1:
                            $('#view-form').find('[name="oType"]').val('Mobil');
                            break;

                        case 2:
                            $('#view-form').find('[name="oType"]').val('Motor');
                            break;
                    }

                    loaderOff();
                    $('#view-modal').modal('show');
                },
                error: function(err) {
                    loaderOff();
                },
            });
        }

        function updateViewStatus4(id) {
            loaderOn();
            $('#update-status-4-form').find('[name="id"]').val(id);
            $.ajax({
                url: '/foreman/order-list/' + id + '/task-list',
                method: 'GET',
                dataType: 'html',
                success: function(rspn) {
                    $('#update-status-4-form').find('#list-table tbody').empty();
                    $('#update-status-4-form').find('#list-table tbody').append(rspn);

                    loaderOff();
                    $('#update-status-4-modal').modal('show');
                },
                error: function(err) {
                    loaderOff();
                },
            });
        }

        function updateViewStatus3(id, name) {
            $('#update-status-3-form').find('[name="id"]').val(id);
            $('#update-status-3-name').text(name);
            $('#update-status-3-modal').modal('show');
        }

    </script>
@endsection