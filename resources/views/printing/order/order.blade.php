@php
    $isNet = $model->priceList->is_net;
    $currency = $model->currency->iso;
    $formatter = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
@endphp
@section('first-page-header')
    <x-flux::print.first-page-header :address="$model->addressInvoice">
        <x-slot:right-block>
            <div class="inline-block">
                @section('first-page-right-block')
                    <div class="inline-block">
                        @section('first-page-right-block.labels')
                            <div class="font-semibold">
                                {{ __('Order no.') }}:
                            </div>
                            <div class="font-semibold">
                                {{ __('Customer no.') }}:
                            </div>
                            <div class="font-semibold">
                                {{ __('Order Date') }}:
                            </div>
                        @show
                    </div>
                    <div class="pl-6 text-right inline-block">
                        @section('first-page-right-block.values')
                            <div>
                                {{ $model->order_number }}
                            </div>
                            <div>
                                {{ $model->addressInvoice->contact->customer_number }}
                            </div>
                            <div>
                                {{ $model->order_date->locale(app()->getLocale())->isoFormat('L') }}
                            </div>
                        @show
                    </div>
                @show
            </div>
        </x-slot:right-block>
    </x-flux::print.first-page-header>
@show
<main>
    @section('header')
        <div class="pt-10 pb-4 prose prose-xs">
            {!! Blade::render(html_entity_decode($model->header ?? ''), ['model' => $model]) !!}
        </div>
    @show
    <div class="pb-6">
        @section('positions')
            <table class="w-full table-auto text-xs">
                <thead class="border-b-2 border-black">
                @section('positions.header')
                    <tr>
                        <th class="pr-8 text-left font-normal">
                            {{ __('Pos.') }}
                        </th>
                        <th class="pr-8 text-left font-normal">
                            {{ __('Name') }}
                        </th>
                        <th class="pr-8 text-center font-normal">
                            {{ __('Amount') }}
                        </th>
                        <th class="text-right font-normal uppercase">
                            {{ __('Sum') }}
                        </th>
                    </tr>
                @show
                </thead>
                @section('positions.positions')
                    @foreach ($model->orderPositions as $position)
                        <x-flux::print.order.order-position :position="$position" :is-net="$isNet" :currency="$currency" :formatter="$formatter" />
                    @endforeach
                @show
            </table>
        @show
    </div>
    @if($summary)
        @section('summary')
            <div class="pb-6">
                <table class="w-full">
                    <tbody class="break-inside-avoid">
                    <tr>
                        <td colspan="3" class="border-b border-black font-semibold">
                            {{ __('Summary') }}
                        </td>
                    </tr>
                    @foreach($summary as $summaryItem)
                        <tr>
                            <td>
                                {{ $summaryItem->slug_position }}
                            </td>
                            <td class="whitespace-nowrap">
                                {{ $summaryItem->name }}
                            </td>
                            <td class="text-right float-right">
                                {{ $formatter->formatCurrency($summaryItem->total_net_price, $currency) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @show
    @endif
    @section('total')
        <table class="w-full pb-16 text-xs">
            <tbody class="break-inside-avoid">
            <tr>
                <td colspan="2" class="border-b-2 border-black font-semibold">
                    {{ __('Total') }}
                </td>
            </tr>
            @section('total.net')
                <tr>
                    <td class="text-right">
                        {{ __('Sum net') }}
                    </td>
                    <td class="text-right w-0 whitespace-nowrap pl-12">
                        {{ $formatter->formatCurrency($model->total_net_price, $currency) }}
                    </td>
                </tr>
            @show
            @section('total.vats')
                @foreach($model->total_vats ?? [] as $vat)
                    <tr>
                        <td class="text-right">
                            {{ __(
                                    'Plus :percentage VAT from :total_net',
                                    [
                                        'percentage' => format_number($vat['vat_rate_percentage'], NumberFormatter::PERCENT),
                                        'total_net' => $formatter->formatCurrency($vat['total_net_price'], $currency)
                                    ]
                                )
                            }}
                        </td>
                        <td class="text-right w-0 whitespace-nowrap pl-12">
                            {{ $formatter->formatCurrency($vat['total_vat_price'], $currency) }}
                        </td>
                    </tr>
                @endforeach
            @show
            @section('total.gross')
                <tr class="font-bold">
                    <td class="text-right">
                        {{ __('Total Gross') }}
                    </td>
                    <td class="text-right w-0 whitespace-nowrap pl-12">
                        {{ $formatter->formatCurrency($model->total_gross_price, $currency) }}
                    </td>
                </tr>
            @show
            </tbody>
        </table>
    @show
    @section('footer')
        <div class="break-inside-avoid prose prose-xs">
            {!! Blade::render(html_entity_decode($model->footer ?? ''), ['model' => $model]) !!}
            {!!
                $model->vatRates()
                    ->distinct()
                    ->pluck('footer_text')
                    ->filter()->map(fn(string $text) => Blade::render(html_entity_decode($text), ['model' => $model]))
                    ->implode('<br>')
            !!}
        </div>
    @show
</main>
