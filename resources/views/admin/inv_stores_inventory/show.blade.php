@extends('layouts.admin')
@section('title')
جرد المخازن
@endsection
@section("css")
<link rel="stylesheet" href="{{ asset('assets/admin/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/admin/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection
@section('contentheader')
حركات مخزنية
@endsection
@section('contentheaderlink')
<a href="{{ route('admin.stores_inventory.index') }}">   جرد المخازن </a>
@endsection
@section('contentheaderactive')
عرض الباتشات
@endsection
@section('content')
<div class="row">
   <div class="col-12">
      <div class="card">
         <div class="card-header">
            <h3 class="card-title card_title_center">تفاصيل أمر  جرد    </h3>
         </div>
         <!-- /.card-header -->
         <div class="card-body">
            <div id="ajax_responce_serarchDivparentpill">
               @if (@isset($data) && !@empty($data))
               <table id="example2" class="table table-bordered table-hover">
                  <tr>
                     <td class="width30"> كود الجرد الالي</td>
                     <td > {{ $data['auto_serial'] }}</td>
                  </tr>
                  <tr>
                     <td class="width30">   تاريخ الجرد </td>
                     <td > {{ $data['inventory_date'] }}</td>
                  </tr>
                  <tr>
                     <td class="width30"> نوع الجرد</td>
                     <td > 
                        @if($data['inventory_type']==1)جرد يومي 
                        @elseif($data['inventory_type']==2)جرد اسبوعي  
                        @elseif($data['inventory_type']==3)جرد شهري  
                        @elseif($data['inventory_type']==4)جرد سنوي  
                        @else     لم يحدد @endif
                     </td>
                  </tr>
                  <tr>
                     <td class="width30">   اجمالي باتشات الجرد </td>
                     <td > {{ $data['total_cost_batches']*(1) }}</td>
                  </tr>
                  =
                  <tr>
                     <td class="width30">       حالة الجرد </td>
                     <td > @if($data['is_closed']==1)  مغلق ومؤرشف @else مازال مفتوح  @endif</td>
                  </tr>
                  <tr>
                     <td class="width30">  تاريخ  الاضافة</td>
                     <td > 
                        @php
                        $dt=new DateTime($data['created_at']);
                        $date=$dt->format("Y-m-d");
                        $time=$dt->format("h:i");
                        $newDateTime=date("A",strtotime($time));
                        $newDateTimeType= (($newDateTime=='AM')?'صباحا ':'مساء'); 
                        @endphp
                        {{ $date }}
                        {{ $time }}
                        {{ $newDateTimeType }}
                        بواسطة 
                        {{ $data['added_by_admin'] }}
                     </td>
                  </tr>
                  <tr>
                     <td class="width30">  تاريخ اخر تحديث</td>
                     <td > 
                        @if($data['updated_by']>0 and $data['updated_by']!=null )
                        @php
                        $dt=new DateTime($data['updated_at']);
                        $date=$dt->format("Y-m-d");
                        $time=$dt->format("h:i");
                        $newDateTime=date("A",strtotime($time));
                        $newDateTimeType= (($newDateTime=='AM')?'صباحا ':'مساء'); 
                        @endphp
                        {{ $date }}
                        {{ $time }}
                        {{ $newDateTimeType }}
                        بواسطة 
                        {{ $data['updated_by_admin'] }}
                        @else
                        لايوجد تحديث
                        @endif
                        @if($data['is_closed']==0)
                        <a href="{{ route('admin.stores_inventory.delete',$data['id']) }}" class="btn btn-sm are_you_shue  btn-danger">حذف</a>   
                        <a href="{{ route('admin.stores_inventory.edit',$data['id']) }}" class="btn btn-sm btn-success">تعديل</a>
                        <button id="load_close_approve_invoice"  class="btn btn-sm btn-primary">تحميل الاعتماد والترحيل</button>
                        @endif
                     </td>
                  </tr>
               </table>
            </div>
            <!--  treasuries_delivery   -->
            <div class="card-header">
               <h3 class="card-title card_title_center">
                  باتشات الاصناف المضافة علي امر الجرد
                  @if($data['is_closed']==0)
                  <button type="button" class="btn btn-info"  data-target="#AddDetailsModal" data-toggle="modal">
                  اضافة باتش صنف جديد للجرد
                  </button>
                  @endif
               </h3>
               <input type="hidden" id="token_search" value="{{csrf_token() }}">
               <input type="hidden" id="ajax_reload_itemsdetials" value="{{ route('admin.stores_inventory.reload_itemsdetials') }}">
               <input type="hidden" id="ajax_reload_parent_pill" value="{{ route('admin.stores_inventory.reload_parent_pill') }}">
               <input type="hidden" id="ajax_load_edit_item_details" value="{{ route('admin.stores_inventory.load_edit_item_details') }}">
               <input type="hidden" id="ajax_load_modal_approve_invoice" value="{{ route('admin.stores_inventory.load_modal_approve_invoice') }}">
               <input type="hidden" id="ajax_load_usershiftDiv" value="{{ route('admin.stores_inventory.load_usershiftDiv') }}">
               <input type="hidden" id="autoserailparent" value="{{ $data['auto_serial'] }}">
               <input type="hidden" id="id_parent_pill" value="{{ $data['id'] }}">
            </div>
            <div id="ajax_responce_serarchDivDetails">
               @if (@isset($details) && !@empty($details) && count($details)>0)
               @php
               $i=1;   
               @endphp
               <table id="example2" class="table table-bordered table-hover">
                  <thead class="custom_thead">
                     <th>مسلسل</th>
                     <th style="width:15%;">كود الباتش</th>
                     <th>اسم الصنف </th>
                     <th> الكمية بالباتش</th>
                     <th> الكمية الدفترية</th>
                     <th> الفرق</th>
                     <th> تكلفة الوحدة</th>
                     <th> اجمالي التكلفة</th>
                     <th>  سبب النقص / الزيادة</th>
                     <th></th>
                     @if($data['is_closed']==0)
                     <th></th>
                     @endif
                  </thead>
                  <tbody>
                     @foreach ($details as $info )
                     <tr>
                        <td>{{ $i }}</td>
                        <td>
                           {{ $info->batch_auto_serial }} <br>
                          @if($info->item_type==2)
                         تاريخ انتاج <br>{{ $info->production_date }} <br>
                         تاريخ انتهاء <br>{{ $info->expired_date }} 

                          @endif
                        </td>
                        <td>{{ $info->item_name }}</td>
                        <td>{{ $info->old_quantity*(1) }}</td>
                        <td>{{ $info->new_quantity*(1) }}</td>
                        <td>{{ $info->diffrent_quantity*(1) }}</td>
                        <td>{{ $info->unit_cost_price*(1) }}</td>
                        <td>{{ $info->total_cost_price*(1) }}</td>
                        <td>{{ $info->notes }}</td>
                        <td > 
                           @php
                           $dt=new DateTime($info->created_at);
                           $date=$dt->format("Y-m-d");
                           $time=$dt->format("h:i");
                           $newDateTime=date("A",strtotime($time));
                           $newDateTimeType= (($newDateTime=='AM')?'صباحا ':'مساء'); 
                           @endphp
                           {{ $date }} <br>
                           {{ $time }}
                           {{ $newDateTimeType }}  <br>
                           بواسطة 
                           {{ $info->added_by_admin}}
                           @if($info->updated_by>0 and $info->updated_by!=null )
                           <br>
                           @php
                           $dt=new DateTime($info->updated_at);
                           $date=$dt->format("Y-m-d");
                           $time=$dt->format("h:i");
                           $newDateTime=date("A",strtotime($time));
                           $newDateTimeType= (($newDateTime=='AM')?'صباحا ':'مساء'); 
                           @endphp
                           {{ $date }}  <br>
                           {{ $time }}
                           {{ $newDateTimeType }}  <br>
                           بواسطة 
                           {{ $data['updated_by_admin'] }}
                           @else
                           لايوجد تحديث
                           @endif
                        </td>
                        @if($data['is_closed']==0)
                        <td>
                           @if($data['is_approved']==0)
                           <button data-id="{{ $info->id }}" class="btn btn-sm load_edit_item_details  btn-primary">تعديل</button>   
                           <button data-id="{{ $info->id }}" class="btn btn-sm do_close_item  btn-danger">ترحيل</button>   
                        
                           @endif
                        </td>
                        @endif
                     </tr>
                     @php
                     $i++; 
                     @endphp
                     @endforeach
                  </tbody>
               </table>
               @else
               <div class="alert alert-danger">
                  عفوا لاتوجد بيانات لعرضها !!
               </div>
               @endif
            </div>
            <!--  End treasuries_delivery   -->
            @else
            <div class="alert alert-danger">
               عفوا لاتوجد بيانات لعرضها !!
            </div>
            @endif
         </div>
      </div>
   </div>
</div>
<div class="modal fade " id="AddDetailsModal">
   <div class="modal-dialog modal-xl" >
      <div class="modal-content bg-info">
         <div class="modal-header">
            <h4 class="modal-title">اضافة باتش اصناف  للفاتورة</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
         </div>
         <div class="modal-body" id="Add_item_Modal_body" style="background-color: white !important; color:black;">
            <form action="{{ route('admin.stores_inventory.add_new_details',$data['id']) }}" method="post" >
            @csrf
            <div class="form-group">
               <label>   ادخال الباتشات الفارغة بالجرد</label>
               <select name="dose_enter_empty_batch" id="dose_enter_empty_batch" class="form-control">
                  <option   value="1">    نعم</option>
                  <option   value="0">    لا</option>
               </select>
            </div>
            <div class="form-group">
               <label>  اضافة كل الاصناف بالمخزن</label>
               <select name="does_add_all_items" id="does_add_all_items" class="form-control">
                  <option   value="1">    نعم</option>
                  <option   value="0">    لا</option>
               </select>
            </div>
            <div class="form-group"  id="ItemsDiv" style="display: none;"  >
               <label>     بيانات الاصناف بالمخزن</label>
               <select style="color:black" name="items_in_store" id="items_in_store" class="form-control select2">
                  <option value="">  اختر الصنف للجرد</option>
                  @if (@isset($items_in_store) && !@empty($items_in_store))
                  @foreach ($items_in_store as $info )
                  <option  value="{{ $info->item_code }}"> {{ $info->name }} </option>
                  @endforeach
                  @endif
               </select>
            </div>
            <div class="form-group text-center">
               <button type="submit" name="submit" id="do_add_itmes" class="btn btn-sm btn-success">أضف للجرد </button>
            </div>
         </div>
         <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-outline-light" data-dismiss="modal">اغلاق</button>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<div class="modal fade " id="edit_item_Modal">
   <div class="modal-dialog modal-xl" >
      <div class="modal-content bg-info">
         <div class="modal-header">
            <h4 class="modal-title text-center">تحديث خدمة  بالفاتورة</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
         </div>
         <div class="modal-body" id="edit_item_Modal_body" style="background-color: white !important; color:black;">
         </div>
         <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-outline-light" data-dismiss="modal">اغلاق</button>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<div class="modal fade " id="ModalApproveInvocie">
   <div class="modal-dialog modal-xl" >
      <div class="modal-content bg-info">
         <div class="modal-header">
            <h4 class="modal-title" style="text-align: center">  اعتماد وترحيل فاتورة خدمات</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
         </div>
         <div class="modal-body" id="ModalApproveInvocie_body" style="background-color: white !important; color:black;">
         </div>
         <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-outline-light" data-dismiss="modal">اغلاق</button>
         </div>
      </div>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>
<div class="modal modal-info" id="alert_message_modal">
   <div class="modal-dialog">
      <div class="modal-content modal-info  bg-info">
         <div class="modal-header">
            <div class="modal-body " style="color: black !important;    background: white !important;
               text-align: center;
               font-size: 1.3vw;" >
               تمت العملية بنجاح<span class="glyphicon glyphicon-ok" ></span>
            </div>
         </div>
         <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
   </div>
</div>
@endsection
@section("script")
<script  src="{{ asset('assets/admin/plugins/select2/js/select2.full.min.js') }}"> </script>
<script>
   //Initialize Select2 Elements
   $('.select2').select2({
     theme: 'bootstrap4'
   });
</script>
<script src="{{ asset('assets/admin/js/inv_stores_inventory.js') }}"></script>
@endsection