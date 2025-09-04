@extends('admin::layouts.admin')

@section('main')
    <!-- Page content -->
    <div id="page-content">
        <!-- Statistics Widgets Header -->
        @include('admin::includes.header-section', ['name' => 'Managers' ])
        <!-- END Statistics Widgets Header -->

        <!-- Responsive Full Block -->
        <div class="block">

            @include('admin::includes.success')


            <!-- Responsive Full Content -->
            @if(count($allData) == 0)
                <br><h3 class="text-center">@lang('Managers Not Found')</h3><br>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Surname</th>
                            <th>Phone</th>
                            <th>Email</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($allData as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->surname }}</td>
                                <td>{{ $item->prefix . $item->phone }}</td>
                                <td>{{ $item->email }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <!-- END Responsive Full Content -->

            @include('admin::includes.paginate', ['data' => $allData ])

        </div>
        <!-- END Responsive Full Block -->
    </div>
    <!-- END Page Content -->
@endsection
