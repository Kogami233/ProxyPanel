@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">规则分组</h2>
				<div class="panel-actions">
					<a href="/rule/group/add" class="btn btn-outline-primary">
						<i class="icon wb-plus" aria-hidden="true"></i>添加分组
					</a>
				</div>
			</div>
			<div class="panel-body">
				<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
					<thead class="thead-default">
					<tr>
						<th> #</th>
						<th> 分组名称</th>
						<th> 审计模式</th>
						<th> 操作</th>
					</tr>
					</thead>
					<tbody>
					@foreach ($ruleGroupList as $ruleGroup)
						<tr>
							<td> {{$ruleGroup->id}} </td>
							<td> {{$ruleGroup->name}} </td>
							<td> {!! $ruleGroup->type_label !!} </td>
							<td>
								<div class="btn-group">
									<a href="/rule/group/assign?id={{$ruleGroup->id}}" class="btn btn-sm btn-outline-primary">
										<i class="icon wb-plus" aria-hidden="true"></i>分配节点
									</a>
									<a href="/rule/group/edit?id={{$ruleGroup->id}}" class="btn btn-sm btn-outline-primary">
										<i class="icon wb-edit"></i>编辑
									</a>
									<button onclick="delRuleGroup('{{$ruleGroup->id}}', '{{$ruleGroup->name}}')" class="btn btn-sm btn-outline-danger">
										<i class="icon wb-trash"></i>删除
									</button>
								</div>
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
			<div class="panel-footer">
				<div class="row">
					<div class="col-sm-4">
						共 <code>{{$ruleGroupList->total()}}</code> 个分组
					</div>
					<div class="col-sm-8">
						<nav class="Page navigation float-right">
							{{$ruleGroupList->links()}}
						</nav>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		// 删除规则分组
		function delRuleGroup(id, name) {
			swal.fire({
				title: '警告',
				text: '确定删除分组 【' + name + '】 ？',
				type: 'warning',
				showCancelButton: true,
				cancelButtonText: '{{trans('home.ticket_close')}}',
				confirmButtonText: '{{trans('home.ticket_confirm')}}',
			}).then((result) => {
				if (result.value) {
					$.post("/rule/group/delete", {_token: '{{csrf_token()}}', id: id}, function (ret) {
						if (ret.status === 'success') {
							swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
								.then(() => window.location.reload())
						} else {
							swal.fire({title: ret.message, type: "error"});
						}
					});
				}
			});
		}
	</script>
@endsection
