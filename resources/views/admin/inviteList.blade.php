@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/bootstrap-table/bootstrap-table.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container-fluid">
		<div class="row">
			<div class="col-md-4">
				<div class="card">
					<div class="card-block">
						<h4 class="card-title cyan-600"><i class="icon wb-plus"></i> {{trans('home.invite_code_make')}}
						</h4>
						<p class="card-text alert alert-info">
							<i class="icon wb-warning red-700"></i> {{trans('home.invite_code_tips1')}}
							<strong>
								10 </strong> {{trans('home.invite_code_tips2', ['days' => \App\Components\Helpers::systemConfig()['user_invite_days']])}}
						</p>
						<button type="button" class="btn btn-primary btn-animate btn-animate-side"
								onclick="makeInvite()"><i class="icon wb-plus"></i> {{trans('home.invite_code_button')}}
						</button>
					</div>
				</div>
			</div>
			<div class="col-md-8">
				<div class="panel">
					<div class="panel-heading">
						<h4 class="panel-title cyan-600"><i
									class="icon wb-extension"></i>{{trans('home.invite_code_my_codes')}}
						</h4>
						<div class="panel-actions">
							<button class="btn btn-primary" onclick="exportInvite()">批量导出</button>
						</div>
					</div>
					<div class="panel-body">
						<table class="text-md-center" data-toggle="table" data-mobile-responsive="true">
							<thead class="thead-default">
							<tr>
								<th> #</th>
								<th> {{trans('home.invite_code_table_name')}} </th>
								<th> {{trans('home.invite_code_table_date')}} </th>
								<th> 生成者</th>
								<th> {{trans('home.invite_code_table_status')}} </th>
								<th> {{trans('home.invite_code_table_user')}} </th>
							</tr>
							</thead>
							<tbody>
							@foreach($inviteList as $invite)
								<tr>
									<td> {{$invite->id}} </td>
									<td>
										<a href="javascript:void(0)" class="mt-clipboard" data-clipboard-action="copy"
												data-clipboard-text="{{url('/register?code='.$invite->code)}}">{{$invite->code}}</a>
									</td>
									<td> {{$invite->dateline}} </td>
									<td>
										{{$invite->uid == 0 ? '系统生成' : (empty($invite->generator) ? '【账号已删除】' : $invite->generator->email)}}
									</td>
									<td>
										{!!$invite->status_label!!}
									</td>
									<td>
										{{$invite->status == 1 ? (empty($invite->user) ? '【账号已删除】' : $invite->user->email) : ''}}
									</td>
								</tr>
							@endforeach
							</tbody>
						</table>
					</div>
					<div class="panel-footer">
						<div class="row">
							<div class="col-md-4">
								{{trans('home.invite_code_summary', ['total' => $inviteList->total()])}}
							</div>
							<div class="col-md-8">
								<nav class="Page navigation float-right">
									{{$inviteList->links()}}
								</nav>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection
@section('script')
	<script src="/assets/custom/Plugin/clipboardjs/clipboard.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/bootstrap-table.min.js" type="text/javascript"></script>
	<script src="/assets/global/vendor/bootstrap-table/extensions/mobile/bootstrap-table-mobile.min.js"
			type="text/javascript"></script>
	<script type="text/javascript">
		// 生成邀请码
		function makeInvite() {
			$.ajax({
				type: "POST",
				url: "/admin/makeInvite",
				async: false,
				data: {_token: '{{csrf_token()}}'},
				dataType: 'json',
				success: function (ret) {
					if (ret.status === 'success') {
						swal.fire({title: ret.message, type: 'success', timer: 1000, showConfirmButton: false})
							.then(() => window.location.reload())
					} else {
						swal.fire({title: ret.message, type: "error"}).then(() => window.location.reload())
					}
				}
			});

			return false;
		}

		// 导出邀请码
		function exportInvite() {
			swal.fire({
				title: '提示',
				text: '确定导出所有邀请码吗',
				type: 'question',
				showCancelButton: true,
				cancelButtonText: '{{trans('home.ticket_close')}}',
				confirmButtonText: '{{trans('home.ticket_confirm')}}',
			}).then((result) => {
				if (result.value) {
					window.location.href = '/admin/exportInvite';
				}
			});
		}

		const clipboard = new ClipboardJS('.mt-clipboard');
		clipboard.on('success', function () {
			swal.fire({
				title: '复制成功',
				type: 'success',
				timer: 1300,
				showConfirmButton: false
			});
		});
		clipboard.on('error', function () {
			swal.fire({
				title: '复制失败，请手动复制',
				type: 'error',
				timer: 1500,
				showConfirmButton: false
			});
		});
	</script>
@endsection
