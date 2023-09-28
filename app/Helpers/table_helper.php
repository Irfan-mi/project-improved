<?php

use App\Models\AppConfigModel;
use App\Models\EmployeeModel;
use App\Models\ItemTaxesModel;

use CodeIgniter\I18n\Time; // Time class for date/time handling

if (!function_exists('getSalesManageTableHeaders')) {

    function getSalesManageTableHeaders()
    {
        $headers = [
            ['sale_id' => lang('common_lang.common_id')],
            ['sale_type' => 'Sale'],
            ['branch_code' => lang('common_lang.sale_branch_code')],
            ['sale_time' => lang('sales_lang.sales_sale_time')],
            ['exact_time' => 'Time'],
            ['customer_name' => lang('customers_lang.customers_customer')],
            ['amount_tendered' => lang('sales_lang.sales_amount_tendered')],
            ['amount_due' => lang('sales_lang.sales_amount_due')],
            ['change_due' => lang('sales_lang.sales_change_due')],
            ['payment_type' => lang('sales_lang.sales_payment_type')],
            ['bill_number' => 'Bill Number'],
        ];

        $appData = (new AppConfigModel())->getAll();
        if ($appData['invoice_enable'] === true) {
            $headers[] = ['invoice_number' => lang('sales_lang.sales_invoice_number')];
            $headers[] = ['invoice' => '&nbsp', 'sortable' => false];
        }

        return transformHeaders(array_merge($headers, [['receipt' => '&nbsp', 'sortable' => false]]));
    }
}

if (!function_exists('getSaleDataLastRow')) {

    function getSaleDataLastRow($sales)
    {
        $sum_amount_tendered = 0;
        $sum_amount_due = 0;
        $sum_change_due = 0;

        foreach ($sales->getResult() as $key => $sale) {
            $sum_amount_tendered += $sale->amount_tendered;
            $sum_amount_due += $sale->amount_due;
            $sum_change_due += $sale->change_due;
        }

        return [
            'sale_id' => '-',
            'sale_time' => '<b>' . lang('sales_lang.sales_total') . '</b>',
            'amount_due' => '<b>' . toCurrency($sum_amount_due) . '</b>',
        ];
    }
}


if (!function_exists('getSaleDataRow')) {

    function getSaleDataRow($sale, $controllerName)
    {
        $appData = (new AppConfigModel())->getAll();

        $saleDateTime = $sale->exact_time;

        if (is_null($saleDateTime)) {
            $saleDateTime = $sale->sale_date;
        } else {
            $saleDate = date('Y-m-d', strtotime($sale->exact_time));

            if ($sale->sale_date == $saleDate) {
                $saleDateTime = date($appData['dateformat'], strtotime($sale->exact_time));
            } else {
                $saleDateTime = date($appData['dateformat'] . ' ' . $appData['timeformat'], strtotime($sale->exact_time));
            }
        }

        $amount_due = $sale->amount_due;
        $sale_type = $sale->sale_type;
        $payment_type = $sale->payment_type;

        if ($amount_due < 1) {
            $sale->amount_due -= $sale->fbr_fee * 2;
            $sale_type .= " refund";
            $payment_type = $sale->sale_payment . ' return';
        }

        $row = [
            'sale_id' => $sale->sale_id,
            'sale_type' => $sale_type,
            'branch_code' => $sale->branch_code,
            'exact_time' => $saleDateTime,
            'sale_time' => date($appData['dateformat'], strtotime($sale->sale_time)),
            'amount_tendered' => toCurrency($sale->amount_tendered),
            'amount_due' => toCurrency($sale->amount_due),
            'change_due' => toCurrency($sale->change_due),
            'payment_type' => $payment_type,
            'bill_number' => $sale->invoice_number,
        ];

        if ($appData['invoice_enable']) {
            $row['invoice_number'] = $sale->invoice_number;
            $row['invoice'] = empty($sale->invoice_number) ? '' : anchor($controllerName . "/invoice/$sale->sale_id", '<span class="glyphicon glyphicon-list-alt"></span>', ['title' => lang('sales_lang.sales_show_invoice')]);
        }

        $row['receipt'] = anchor($controllerName . "/receipt/$sale->sale_id", '<span class="glyphicon glyphicon-usd"></span>', ['title' => lang('sales_lang.sales_show_receipt')]);
        $row['edit'] = anchor($controllerName . "/edit/$sale->sale_id", '<span class="glyphicon glyphicon-edit"></span>', ['class' => "modal-dlg print_hide", 'data-btn-delete' => lang('common_lang.common_delete'), 'data-btn-submit' => lang('common_lang.common_submit'), 'title' => lang($controllerName . '_lang.' . $controllerName . '_update')]);

        return $row;
    }
}

if (!function_exists('getSalesManagePaymentsSummary')) {

    function getSalesManagePaymentsSummary($payments, $sales)
    {
        $table = '<div id="report_summary">';

        foreach ($payments as $key => $payment) {
            $amount = $payment['payment_amount'];
            if ($payment['payment_type'] == lang('sales_lang.sales_cash')) {
                foreach ($sales as $key => $sale) {
                    $amount -= $sale['change_due'];
                }
            }
            $table .= '<div class="summary_row">' . $payment['payment_type'] . ': ' . toCurrency($amount) . '</div>';
        }
        $table .= '</div>';

        return $table;
    }
}

if (!function_exists('transformHeadersReadonly')) {

    function transformHeadersReadonly($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[] = ['field' => $key, 'title' => $value, 'sortable' => $value != '', 'switchable' => !preg_match('(^$|&nbsp;)', $value)];
        }

        return json_encode($result);
    }
}



if (!function_exists('transformHeaders')) {

    function transformHeaders($array)
    {
        $result = [];
        $array = array_merge([['checkbox' => 'select', 'sortable' => false]], $array, [['edit' => '']], [['biometric' => '']]);

        foreach ($array as $element) {
            $result[] = [
                'field' => key($element),
                'title' => current($element),
                'switchable' => isset($element['switchable']) ? $element['switchable'] : !preg_match('(^$|&nbsp)', current($element)),
                'sortable' => isset($element['sortable']) ? $element['sortable'] : current($element) !== '',
                'checkbox' => isset($element['checkbox']) ? $element['checkbox'] : false,
                'class' => isset($element['checkbox']) || preg_match('(^$|&nbsp)', current($element)) ? 'print_hide' : '',
            ];
        }

        return json_encode($result);
    }
}

if (!function_exists('getPeopleManageTableHeaders')) {

    function getPeopleManageTableHeaders()
    {
        $session = \Config\Services::session();
        $employee = new EmployeeModel();

        $headers = [
            ['people.person_id' => lang('common_lang.common_id')],
            ['last_name' => lang('common_lang.common_last_name')],
            ['first_name' => lang('common_lang.common_first_name')],
            ['email' => lang('common_lang.common_email')],
            ['phone_number' => lang('common_lang.common_phone_number')],
        ];

        if ($employee->hasGrant('messages', $session->get('person_id'))) {
            $headers[] = ['messages' => '', 'sortable' => false];
        }

        return transformHeaders($headers);
    }
}

if (!function_exists('getPersonDataRow')) {

    function getPersonDataRow($person, $controllerName)
    {
        return [
            'people.person_id' => $person->person_id,
            'last_name' => $person->last_name,
            'first_name' => $person->first_name,
            'email' => empty($person->email) ? '' : mailto($person->email, $person->email),
            'phone_number' => $person->phone_number,
            'messages' => empty($person->phone_number) ? '' : anchor("Messages/view/{$person->person_id}", '<span class="glyphicon glyphicon-phone"></span>', ['class' => 'modal-dlg', 'data-btn-submit' => lang('common_lang.common_submit'), 'title' => lang('messages_lang.messages_sms_send')]),
            'edit' => anchor("{$controllerName}/view/{$person->person_id}", '<span class="glyphicon glyphicon-edit"></span>', ['class' => 'modal-dlg', 'data-btn-submit' => lang('common_lang.common_submit'), 'title' => lang("{$controllerName}_lang.{$controllerName}_update")]),
            'biometric' => anchor("{$controllerName}/biometric/{$person->person_id}", '<span class="glyphicon glyphicon-fingerprint"></span>', ['class' => 'modal-dlg', 'data-btn-submit' => lang('common_lang.common_submit'), 'title' => lang('common_lang.employee_biometric')]),
        ];
    }
}

if (!function_exists('getSuppliersManageTableHeaders')) {

    function getSuppliersManageTableHeaders()
    {
        $session = \Config\Services::session();
        $employee = new EmployeeModel();
        $headers = [
            ['people.person_id' => lang('common_lang.common_id')],
            ['company_name' => lang('suppliers_lang.suppliers_company_name')],
            ['agency_name' => lang('suppliers_lang.suppliers_agency_name')],
            ['last_name' => lang('common_lang.common_last_name')],
            ['first_name' => lang('common_lang.common_first_name')],
            ['email' => lang('common_lang.common_email')],
            ['phone_number' => lang('common_lang.common_phone_number')],
        ];

        if ($employee->hasGrant('messages', $session->get('person_id'))) {
            $headers[] = ['messages' => ''];
        }

        return transformHeaders($headers);
    }
}

if (!function_exists('getSupplierDataRow')) {

    function getSupplierDataRow($supplier, $controllerName)
    {
        return [
            'people.person_id' => $supplier->person_id,
            'company_name' => $supplier->company_name,
            'agency_name' => $supplier->agency_name,
            'last_name' => $supplier->last_name,
            'first_name' => $supplier->first_name,
            'email' => empty($supplier->email) ? '' : mailto($supplier->email, $supplier->email),
            'phone_number' => $supplier->phone_number,
            'messages' => empty($supplier->phone_number) ? '' : anchor("Messages/view/$supplier->person_id", '<span class="glyphicon glyphicon-phone"></span>', ['class' => "modal-dlg", 'data-btn-submit' => lang('common_lang.common_submit'), 'title' => lang('messages_lang.messages_sms_send')]),
            'edit' => anchor("$controllerName/view/$supplier->person_id", '<span class="glyphicon glyphicon-edit"></span>', ['class' => "modal-dlg", 'data-btn-submit' => lang('common_lang.common_submit'), 'title' => lang($controllerName . '_lang.' . $controllerName . '_update')])
        ];
    }
}

if (!function_exists('getItemsManageTableHeaders')) {

    function getItemsManageTableHeaders()
    {
        $headers = [
            ['items.item_id' => lang('common_lang.common_id')],
            ['item_number' => lang('items_lang.items_item_number')],
            ['name' => lang('items_lang.items_name')],
            ['category' => lang('items_lang.items_category')],
            ['company_name' => lang('items_lang.suppliers_company_name')],
            ['cost_price' => lang('items_lang.items_cost_price')],
            ['unit_price' => lang('items_lang.items_unit_price')],
            ['quantity' => lang('items_lang.items_quantity')],
            ['allow_discount' => lang('sales_lang.sales_customer_discount')],
            ['tax_percents' => lang('items_lang.items_tax_percents'), 'sortable' => false],
            ['item_pic' => lang('items_lang.items_image'), 'sortable' => false],
            ['inventory' => ''],
            ['stock' => '']
        ];

        return transformHeaders($headers);
    }
}

if (!function_exists('getItemDataRow')) {

    function getItemDataRow($item, $controllerName)
    {
        $itemTaxModal = new ItemTaxesModel();
        $itemTaxInfo = $itemTaxModal->getTaxInfo($item->item_id);
        $tax_percents = '';

        foreach ($itemTaxInfo as $taxInfo) {
            $tax_percents .= toTaxDecimals($taxInfo['percent']) . '%, ';
        }

        $tax_percents = rtrim($tax_percents, ', '); // Remove ', ' from the last item
        $image = '';

        if (!empty($item->pic_id)) {
            $images = glob("uploads/item_pics/{$item->pic_id}.*");

            if (sizeof($images) > 0) {
                $image .= '<a class="rollover" href="' . base_url($images[0]) . '"><img src="' . site_url('items/pic_thumb/' . $item->pic_id) . '"></a>';
            }
        }

        return [
            'items.item_id' => $item->item_id,
            'item_number' => $item->item_number,
            'name' => $item->name,
            'category' => $item->category,
            'company_name' => $item->company_name,
            'cost_price' => toCurrency($item->cost_price),
            'unit_price' => toCurrency($item->unit_price),
            'quantity' => toQuantityDecimals($item->quantity),
            'allow_discount' => $item->custom1,
            'tax_percents' => !$tax_percents ? '-' : $tax_percents,
            'item_pic' => $image,
            'inventory' => anchor("{$controllerName}/inventory/{$item->item_id}", '<span class="glyphicon glyphicon-pushpin"></span>', [
                'class' => 'modal-dlg',
                'data-btn-submit' => lang('common_lang.common_submit'),
                'title' => lang("{$controllerName}_lang.{$controllerName}_count")
            ]),
            'stock' => anchor("{$controllerName}/count_details/{$item->item_id}", '<span class="glyphicon glyphicon-list-alt"></span>', [
                'class' => 'modal-dlg',
                'title' => lang("{$controllerName}_lang.{$controllerName}_details_count")
            ]),
            'edit' => anchor("{$controllerName}/view/{$item->item_id}", '<span class="glyphicon glyphicon-edit"></span>', [
                'class' => 'modal-dlg',
                'data-btn-submit' => lang('common_lang.common_submit'),
                'title' => lang("{$controllerName}_lang.{$controllerName}_update")
            ]),
        ];
    }
}

if (!function_exists('getGiftcardsManageTableHeaders')) {

    function getGiftcardsManageTableHeaders()
    {
        $headers = [
            ['giftcard_id' => lang('common_lang.common_id')],
            // ['last_name' => lang('common_lang.common_last_name')],
            // ['first_name' => lang('common_lang.common_first_name')],
            ['giftcard_number' => lang('giftcards_lang.giftcards_giftcard_number')],
            ['value' => lang('giftcards_lang.giftcards_card_value')],
            ['expired' => lang('giftcards_lang.giftcards_card_expired')],
            ['status' => lang('giftcards_lang.giftcards_card_status')],
        ];

        return transformHeaders($headers);
    }
}

if (!function_exists('getGiftcardDataRow')) {

    function getGiftcardDataRow($giftcard, $controllerName)
    {
        $status = '';

        $expiresAt = new Time($giftcard->expires_at);

        if (Time::now() > $expiresAt) {
            $status = '<span class="badge badge-danger"></span>' . lang('giftcards_lang.giftcards_card_expired_expired');
        } elseif ($giftcard->status == 1) {
            $status = '<span class="badge badge-success"></span>' . lang('giftcards_lang.giftcards_card_used');
        } else {
            $status = '<span class="badge badge-info"></span>' . lang('giftcards_lang.giftcards_card_unused');
        }

        return [
            'giftcard_id' => $giftcard->giftcard_id,
            'giftcard_number' => $giftcard->giftcard_number,
            'value' => toCurrency($giftcard->value),
            'expired' => $expiresAt->toLocalizedString('yyyy-MM-dd HH:mm:ss'),
            'status' => $status,
            'edit' => anchor("{$controllerName}/view/{$giftcard->giftcard_id}", '<span class="glyphicon glyphicon-edit"></span>', [
                'class' => 'modal-dlg',
                'data-btn-submit' => lang('common_lang.common_submit'),
                'title' => lang("{$controllerName}_lang.{$controllerName}_update"),
            ]),
        ];
    }
}

if (!function_exists('getItemKitsManageTableHeaders')) {

    function getItemKitsManageTableHeaders()
    {
        $headers = [
            ['item_kit_id' => lang('item_kits_lang.item_kits_kit')],
            ['name' => lang('item_kits_lang.item_kits_name')],
            ['description' => lang('item_kits_lang.item_kits_description')],
            ['cost_price' => lang('items_lang.items_cost_price'), 'sortable' => false],
            ['unit_price' => lang('items_lang.items_unit_price'), 'sortable' => false]
        ];

        return transformHeaders($headers);
    }
}

if (!function_exists('getItemKitDataRow')) {

    function getItemKitDataRow($item_kit, $controllerName)
    {
        return [
            'item_kit_id' => $item_kit->item_kit_id,
            'name' => $item_kit->name,
            'description' => $item_kit->description,
            'cost_price' => toCurrency($item_kit->total_cost_price),
            'unit_price' => toCurrency($item_kit->total_unit_price),
            'edit' => anchor("$controllerName/view/$item_kit->item_kit_id", '<span class="glyphicon glyphicon-edit"></span>', [
                'class' => 'modal-dlg',
                'data-btn-submit' => lang('common_lang.common_submit'),
                'title' => lang($controllerName . '_lang.' . $controllerName . '_update'),
            ]),
        ];
    }
}

if (!function_exists('getDeletedSalesTableHeaders')) {

    function getDeletedSalesTableHeaders()
    {
        $headers = [
            ['employee_name' => lang('common_lang.employee_name')],
            ['item_name' => lang('sales_lang.sales_item_name')],
            ['quantity' => lang('sales_lang.sales_quantity')],
            ['price' => lang('sales_lang.sales_price')],
            ['deleted_time' => lang('sales_lang.sales_deleted_time')],
        ];

        return transformHeaders(array_merge($headers, []));
    }
}

if (!function_exists('getSaleDeletedLogDataRow')) {

    function getSaleDeletedLogDataRow($sale)
    {
        $appData = (new AppConfigModel())->getAll();

        $timeFormat = $appData['timeformat'];

        $row = [
            'employee_name' => $sale->username,
            'item_name' => $sale->name,
            'quantity' => $sale->quantity,
            'price' => $sale->price,
            'deleted_time' => date($timeFormat, strtotime($sale->deleted_time)),
        ];

        return $row;
    }
}
