<div class="row" id="quick_product_opening_stock_div">
	<div class="col-sm-12">
		<h4>@lang('lang_v1.add_opening_stock')</h4>
	</div>
	<div class="col-sm-12">
		<table class="table table-condensed table-th-green" id="quick_product_opening_stock_table">
			<thead>
			<tr>
				<th>@lang('sale.location')</th>
				<th>@lang( 'lang_v1.quantity' )</th>
				<th>@lang( 'purchase.unit_cost_before_tax' )</th>
				@if($enable_expiry)
					<th>@lang('lang_v1.expiry_date')</th>
				@endif
				@if($enable_lot)
					<th>@lang( 'lang_v1.lot_number' )</th>
				@endif
				<th>@lang( 'purchase.subtotal_before_tax' )</th>
			</tr>
			</thead>
			<tbody>
		@foreach($locations as $key => $value)
			@php
				$os = $opening_stock_data[$key] ?? null;
				$qty = $os ? $os['quantity'] : 0;
				$price = $os ? $os['purchase_price'] : null;
				$exp = $os ? $os['exp_date'] : null;
				$lot = $os ? $os['lot_number'] : null;
				$subtotal = ($qty && $price) ? ($qty * $price) : 0;
			@endphp
			<tr>
				<td>{{$value}}</td>
				<td>{!! Form::text('opening_stock[' . $key . '][quantity]', $qty, ['class' => 'form-control input-sm input_number purchase_quantity', 'required']); !!}</td>
				<td>{!! Form::text('opening_stock[' . $key . '][purchase_price]', $price, ['class' => 'form-control input-sm input_number unit_price', 'required']); !!}</td>
				@if($enable_expiry)
					<td>
						{!! Form::text('opening_stock[' . $key . '][exp_date]', $exp , ['class' => 'form-control input-sm os_exp_date', 'readonly']); !!}
					</td>
				@endif
				@if($enable_lot)
					<td>
						{!! Form::text('opening_stock[' . $key . '][lot_number]', $lot , ['class' => 'form-control input-sm']); !!}
					</td>
				@endif
				<td>
					<span class="row_subtotal_before_tax">{{ $subtotal }}</span>
				</td>
			</tr>
		@endforeach
		</tbody>
		</table>
	</div>
</div>
