@extends('layouts.app')

@section('content')
<?php
    $user_temp = json_decode($user);
    $user_id = $user_temp->id; 
    $user_role = strtolower($user_temp->title); 
    $user_name = $user_temp->name; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center text-primary">{{__('Malfunctions list')}}</h3>
                </div>
                <div class="card-body">
                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif
                    @if ($user_role == 'admin' || $user_role == 'employee')
                        <a href="{{ action('MalfunctionController@create') }}" class="btn-blue"><i class="fa fa-plus-circle"></i> {{__('New form')}}</a>
                        <a href="{{ action('MalfunctionController@createGuidance') }}" class="btn-blue"><i class="fa fa-plus-circle"></i> {{__('New guidance day')}}</a>
                    @endif
                    <form action="">
                        <input type="text" name="date-range" value="" placeholder="{{__('Select date range')}}">
                    </form>
                    <table class="table table-striped" border="1" id="malfunctions-table">
                        <thead style="background-color:#0074D9;color:white;">
                        <tr>
                            <th data-index='0' data-sort="string" class="sortStyle">{{__('Form')}} #</th>
                            <th data-index='1' data-sort="string" class="sortStyle">{{__('Date')}}</th>
                            <th data-index='2' data-sort="string" class="sortStyle">
                                <span class="{{app()->getLocale() == 'he' ? 'pull-right' : 'pull-left'}}">{{__('Employee')}} </span><i class="fa fa-search search-toggle"></i>
                                <input type="text" class="{{ (isset($employee)  && $employee) ? "" : "hidden" }} search-data" value="{{ (isset($employee)  && $employee) ? $employee : "" }}" name="employee" size="15" placeholder="{{__('Employee')}}" />

                            </th>
                            <th data-index='3' data-sort="string" class="sortStyle">{{__('Score')}}</th>
                            <th data-index='4' data-sort="string" class="sortStyle">
                                <span class="{{app()->getLocale() == 'he' ? 'pull-right' : 'pull-left'}}">{{__('Site')}} </span><i class="fa fa-search search-toggle"></i>
                                <input type="text" class="{{ (isset($site)  && $site) ? "" : "hidden"}} search-data" value="{{ (isset($site)  && $site) ? $site : ""}}" name="site" size="15" placeholder="{{__('Site')}}" />
                            </th>
                            <th data-index='5' data-sort="string" class="sortStyle">
                                <span class="{{app()->getLocale() == 'he' ? 'pull-right' : 'pull-left'}}">{{__('Sub-site')}} </span><i class="fa fa-search search-toggle"></i>
                                <input type="text" class="{{ (isset($subsite)  && $subsite) ? "" : "hidden"}} search-data" value="{{ (isset($subsite)  && $subsite) ? $subsite : ""}}" name="subsite" size="15" placeholder="{{__('Sub-Site')}}" />
                            </th>
                            <th data-index='6' data-sort="string" class="sortStyle">{{__('Status')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($malfunctions as $malfunction)
                                @if($user_role != 'admin' && $user_role != 'employee' && isset($malfunction->data['status']) && isset($malfunction->data['status']['stage']) && $malfunction->data['status']['stage'] != 'publish')
                                    @continue;
                                @endif

                                <?php
                                    $date = '--/--/--';
                                    $date_sort_value = '';
                                    $date_pieces = explode('-', $malfunction->data['date']);
                                    if(count($date_pieces) == 3) {
                                        $date_sort_value = strtotime((2000 + (int)$date_pieces[2]).'-'.$date_pieces[1].'-'.$date_pieces[0]);
                                        $date = date("d/m/y", $date_sort_value);
                                    }

                                    $admin_changed = '';
                                    if($user_role == 'employee' && isset($malfunction->data['employee_name']) && $user_name == $malfunction->data['employee_name'] && isset($malfunction->data['status']) && isset($malfunction->data['status']['admin_changed_date']) &&
                                        (!isset($malfunction->data['status']['users']) || !isset($malfunction->data['status']['users'][$user_id]) || strtotime($malfunction->data['status']['users'][$user_id]['last_visit_date']) < strtotime($malfunction->data['status']['admin_changed_date']))) {
                                        $admin_changed  = 'changed';
                                    }

                                    $link_address = action('MalfunctionController@show', $malfunction->id);
                                    if(!isset($malfunction->data['status']) || !isset($malfunction->data['status']['stage']) || $malfunction->data['status']['stage'] == 'draft') {
                                        $link_address = action('MalfunctionController@edit', $malfunction->id);
                                    }
                                ?>
                                <tr data-type='malfunction' data-id='{{ $malfunction->id }}' class="{{$admin_changed}}">
                                    <td><a href="{{ $link_address }}">{{ $malfunction->nameCode }}</a></td>
                                    <td data-sort-value='{{$date_sort_value}}'>{{ $date }}</td>
                                    <td>{{isset($malfunction->data['employee_name']) ? $malfunction->data['employee_name'] : "------"}}</td>
                                    <td> {{ isset($malfunction->data['calculate']['total']) ? $malfunction->data['calculate']['total'] : "--.-%" }} </td>
                                    <td>{{isset($malfunction->data['site']) ? $malfunction->data['site'] : "------"}}</td>
                                    <td>{{isset($malfunction->data['subsite']) ? $malfunction->data['subsite'] : "------"}}</td>
                                    <td class='status'>
                                        @if (isset($malfunction->data['status']) && isset($malfunction->data['status']['stage']) && 
                                            ((($user_role == 'admin' || $user_role == 'employee') && $malfunction->data['status']['stage'] != 'draft') || ($user_role == 'client' || $user_role == 'contractor') && $malfunction->data['status']['stage'] == 'publish'))
                                            <label class="toggle">
                                                <input type="checkbox" name="toggle-status" @if (isset($malfunction->data['status']) && isset($malfunction->data['status']['stage']) && $malfunction->data['status']['stage'] == 'publish') checked='checked' @endif }} @if ($user_role != 'admin') disabled @endif>
                                                <i data-swchon-text="{{__('ON')}}" data-swchoff-text="{{__('OFF')}}"></i>
                                            </label>
                                        @endif
                                        @if (!isset($malfunction->data['status']) || !isset($malfunction->data['status']['stage']) || $malfunction->data['status']['stage'] == 'draft')
                                            <img src="{{url('/')}}/img/draft.png" width=40 style='margin-left:5px;'>
                                        @endif
                                        @if ($user_role == 'admin' && isset($malfunction->data['employee_id']) && isset($malfunction->data['status']) && isset($malfunction->data['status']['admin_changed_date']))
                                            <?php $employee_id = $malfunction->data['employee_id']; ?>
                                            @if (isset($malfunction->data['status']['users']) && isset($malfunction->data['status']['users'][$employee_id]) && strtotime($malfunction->data['status']['users'][$employee_id]['last_visit_date']) > strtotime($malfunction->data['status']['admin_changed_date']))
                                                <i class='fa fa-thumbs-up'></i>
                                            @endif
                                        @endif
                                        @if (isset($malfunction->data['status']) && isset($malfunction->data['status']['stage']))
                                            @if(isset($malfunction->data['status']['last_comment_date']) && (!isset($malfunction->data['status']['users']) || !isset($malfunction->data['status']['users'][$user_id]) || strtotime($malfunction->data['status']['users'][$user_id]['last_visit_date']) < strtotime($malfunction->data['status']['last_comment_date'])))
                                                <img src="{{url('/')}}/img/exclamation.png" width=30>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @foreach($guidances as $guidance)
                                @if($user_role != 'admin' && $user_role != 'employee' && isset($guidance->data['status']) && isset($guidance->data['status']['stage']) && $guidance->data['status']['stage'] != 'publish')
                                    @continue;
                                @endif

                                <?php
                                    $date = '--/--/--';
                                    $date_sort_value = '';
                                    $date_pieces = explode('-', $guidance->data['date']);
                                    if(count($date_pieces) == 3) {
                                        $date_sort_value = strtotime((2000 + (int)$date_pieces[2]).'-'.$date_pieces[1].'-'.$date_pieces[0]);
                                        $date = date("d/m/y", $date_sort_value);
                                    }

                                    $admin_changed = '';
                                    if($user_role == 'employee' && isset($guidance->data['employee_name']) && $user_name == $guidance->data['employee_name'] && isset($guidance->data['status']) && isset($guidance->data['status']['admin_changed_date']) &&
                                        (!isset($guidance->data['status']['users']) || !isset($guidance->data['status']['users'][$user_id]) || strtotime($guidance->data['status']['users'][$user_id]['last_visit_date']) < strtotime($guidance->data['status']['admin_changed_date']))) {
                                        $admin_changed  = 'changed';
                                    }

                                    $link_address = action('MalfunctionController@showGuidance', $guidance->id);
                                    if(!isset($guidance->data['status']) || !isset($guidance->data['status']['stage']) || $guidance->data['status']['stage'] == 'draft') {
                                        $link_address = action('MalfunctionController@editGuidance', $guidance->id);
                                    }
                                ?>
                                <tr data-type='guidance' data-id='{{ $guidance->id }}' class="{{$admin_changed}}">
                                    <td><a href="{{ $link_address }}">{{ $guidance->nameCode }}</a></td>
                                    <td data-sort-value='{{$date_sort_value}}'>{{ $date }}</td>
                                    <td>{{isset($guidance->data['employee_name']) ? $guidance->data['employee_name'] : "------"}}</td>
                                    <td> {{__('Guidance day')}} </td>
                                    <td>{{isset($guidance->data['site']) ? $guidance->data['site'] : "------"}}</td>
                                    <td>{{isset($guidance->data['subsite']) ? $guidance->data['subsite'] : "------"}}</td>
                                    <td class='status'>
                                        @if (isset($guidance->data['status']) && isset($guidance->data['status']['stage']) && 
                                            ((($user_role == 'admin' || $user_role == 'employee') && $guidance->data['status']['stage'] != 'draft') || ($user_role == 'client' || $user_role == 'contractor') && $guidance->data['status']['stage'] == 'publish'))
                                            <label class="toggle">
                                                <input type="checkbox" name="toggle-status" @if (isset($guidance->data['status']) && isset($guidance->data['status']['stage']) && $guidance->data['status']['stage'] == 'publish') checked='checked' @endif }} @if ($user_role != 'admin') disabled @endif >
                                                <i data-swchon-text="{{__('ON')}}" data-swchoff-text="{{__('OFF')}}"></i>
                                            </label>
                                        @endif
                                        @if (!isset($guidance->data['status']) || !isset($guidance->data['status']['stage']) || $guidance->data['status']['stage'] == 'draft')
                                            <img src="{{url('/')}}/img/draft.png" width=40 style='margin-left:5px;'>
                                        @endif
                                        @if ($user_role == 'admin' && isset($guidance->data['employee_id']) && isset($guidance->data['status']) && isset($guidance->data['status']['admin_changed_date']))
                                            <?php $employee_id = $guidance->data['employee_id']; ?>
                                            @if (isset($guidance->data['status']['users']) && isset($guidance->data['status']['users'][$employee_id]) && strtotime($guidance->data['status']['users'][$employee_id]['last_visit_date']) > strtotime($guidance->data['status']['admin_changed_date']))
                                                <i class='fa fa-thumbs-up'></i>
                                            @endif
                                        @endif
                                        @if (isset($guidance->data['status']) && isset($guidance->data['status']['stage']))
                                            @if(isset($guidance->data['status']['last_comment_date']) && (!isset($guidance->data['status']['users']) || !isset($guidance->data['status']['users'][$user_id]) || strtotime($guidance->data['status']['users'][$user_id]['last_visit_date']) < strtotime($guidance->data['status']['last_comment_date'])))
                                                <img src="{{url('/')}}/img/exclamation.png" width=30>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="{{ url('/') }}/css/malfunctions/main.css">

    @if (app()->getLocale() == 'he')
        <link rel="stylesheet" href="{{ url('/') }}/css/malfunctions/main_rtl.css" media="all">
    @endif

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{url('/')}}/js/stupidtable.min.js"></script>
    <script src="{{url('/')}}/js/malfunctions/index.js"></script>

    <script type="text/javascript">
        var user = <?php echo $user ?>,
            FROM_DATE = "{{ isset($from) ? $from : '' }}", 
            TO_DATE = "{{ isset($to) ? $to : '' }}",
            FORM_LIST_URL = "{{ action('MalfunctionController@index') }}",
            CHANGE_STATUS_URL = "{{ route('changeStatus') }}";


        $('[name="date-range"]').daterangepicker({
            opens: 'left',
            language: 'he',
            autoUpdateInput: false,
            startDate: FROM_DATE != "" ? new Date(FROM_DATE) : undefined,
            endDate: TO_DATE != "" ? new Date(TO_DATE) : undefined,
            locale: {
                applyLabel: "{{__('Apply')}}",
                cancelLabel: "{{__('Clear')}}",
                daysOfWeek: ["{{__('Su')}}", "{{__('Mo')}}", "{{__('Tu')}}", "{{__('We')}}", "{{__('Th')}}", "{{__('Fr')}}", "{{__('Sa')}}"],
                monthNames: [ "{{__('January')}}", "{{__('February')}}", "{{__('March')}}", "{{__('April')}}", "{{__('May')}}", "{{__('June')}}", "{{__('July')}}", "{{__('August')}}", "{{__('September')}}", "{{__('October')}}", "{{__('November')}}", "{{__('December')}}"], 
                firstDay: 1
            },
            onSelect: function(dateText) {
                $('[name="date-range"]').val(FROM_DATE + ' - ' + TO_DATE);
            }
        }, function(start, end, label) {
            location.href = FORM_LIST_URL + "?from=" + start.format('YYYY-MM-DD') + "&to=" + end.format('YYYY-MM-DD');
        });

    </script>
@stop