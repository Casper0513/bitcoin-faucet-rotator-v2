<table class="table table-responsive" id="roles-table">
    <thead>
        <th>Name</th>
        <th>Display Name</th>
        <th>Description</th>
        <th colspan="3">Action</th>
    </thead>
    <tbody>
    @foreach($roles as $role)
        <tr>
            <td>{!! $role->name !!}</td>
            <td>{!! $role->display_name !!}</td>
            <td>{!! $role->description !!}</td>
            <td>
                {!! Form::open(['route' => ['roles.destroy', $role->slug], 'method' => 'delete']) !!}
                <div class='btn-group'>
                    <a href="{!! route('roles.show', [$role->slug]) !!}" class='btn btn-default btn-xs'><i class="glyphicon glyphicon-eye-open"></i></a>
                    <a href="{!! route('roles.edit', [$role->slug]) !!}" class='btn btn-default btn-xs'><i class="glyphicon glyphicon-edit"></i></a>
                </div>
                {!! Form::close() !!}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>