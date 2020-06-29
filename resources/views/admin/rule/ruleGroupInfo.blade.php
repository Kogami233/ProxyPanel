@extends('admin.layouts')
@section('css')
	<link href="/assets/global/vendor/multi-select/multi-select.min.css" type="text/css" rel="stylesheet">
@endsection
@section('content')
	<div class="page-content container">
		<div class="panel">
			<div class="panel-heading">
				<h2 class="panel-title">@isset($ruleGroup)编辑@else添加@endisset规则分组</h2>
				<div class="panel-actions">
					<a href="/rule/group/list" class="btn btn-danger">返 回</a>
				</div>
			</div>
			@if (Session::has('successMsg'))
				<div class="alert alert-success alert-dismissible">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">×</span></button>
					{{Session::get('successMsg')}}
				</div>
			@endif
			@if($errors->any())
				<div class="alert alert-danger alert-dismissible">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">×</span></button>
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			<div class="panel-body">
				<form action=@isset($ruleGroup){{url('/rule/group/edit')}}@else{{url('/rule/group/add')}}@endisset method="post" enctype="multipart/form-data" class="form-horizontal">
					@isset($ruleGroup)<input name="id" value="{{$ruleGroup->id}}" hidden/>@endisset
					@csrf
					<div class="form-group row">
						<label class="col-md-2 col-sm-3 col-form-label" for="name">分组名称</label>
						<div class="col-md-9 col-sm-9">
							<input type="text" class="form-control" name="name" id="name"/>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-2 col-sm-3 col-form-label" for="type">审计模式</label>
						<div class="col-md-10 col-sm-8">
							<ul class="list-unstyled list-inline">
								<li class="list-inline-item">
									<div class="radio-custom radio-primary">
										<input type="radio" name="type" id="block" value="1" checked/>
										<label for="block">阻断</label>
									</div>
								</li>
								<li class="list-inline-item">
									<div class="radio-custom radio-primary">
										<input type="radio" name="type" id="unblock" value="0"/>
										<label for="unblock">放行</label>
									</div>
								</li>
							</ul>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-2 col-sm-3 col-form-label" for="rules">选择规则</label>
						<div class="col-md-9 col-sm-9">
							<div class="btn-group mb-20">
								<button type="button" class="btn btn-primary" id="select-all">全 选</button>
								<button type="button" class="btn btn-danger" id="deselect-all">清 空</button>
							</div>
							<select class="form-control" name="rules[]" id="rules" data-plugin="multiSelect" multiple>
								@foreach($ruleList as $rule)
									<option value="{{$rule->id}}">{{$rule->id . ' - ' . $rule->name}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="form-actions text-right">
						<button type="submit" class="btn btn-success">提 交</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@section('script')
	<script src="/assets/global/vendor/multi-select/jquery.multi-select.js" type="text/javascript"></script>
	<script src="/assets/global/js/Plugin/multi-select.js"></script>
	<script src="/assets/global/js/jquery.quicksearch.js" type="text/javascript"></script>
	<script type="text/javascript">
		@isset($ruleGroup)
		$(document).ready(function () {
			$('#name').val('{{$ruleGroup->name}}');
			$("input[name='type'][value='{{$ruleGroup->type}}']").click();
			$('#rules').multiSelect('select',{!! json_encode(explode(', ', $ruleGroup->rules)) !!});
		})
		@endisset
		// 权限列表
		$('#rules').multiSelect({
			selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='待分配规则，此处可搜索'>",
			selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off' placeholder='已分配规则，此处可搜索'>",
			afterInit: function () {
				const that = this,
					$selectableSearch = that.$selectableUl.prev(),
					$selectionSearch = that.$selectionUl.prev(),
					selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
					selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

				that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
					.on('keydown', function (e) {
						if (e.which === 40) {
							that.$selectableUl.focus();
							return false;
						}
					});

				that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
					.on('keydown', function (e) {
						if (e.which === 40) {
							that.$selectionUl.focus();
							return false;
						}
					});
			},
			afterSelect: function () {
				this.qs1.cache();
				this.qs2.cache();
			},
			afterDeselect: function () {
				this.qs1.cache();
				this.qs2.cache();
			}
		});

		// 全选
		$('#select-all').click(function () {
			$('#rules').multiSelect('select_all');
			return false;
		});

		// 反选
		$('#deselect-all').click(function () {
			$('#rules').multiSelect('deselect_all');
			return false;
		});
	</script>
@endsection
