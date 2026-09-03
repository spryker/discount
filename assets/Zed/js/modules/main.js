/**
 * Copyright (c) 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

var SqlFactory = require('./libs/sql-factory');

require('jquery-datetimepicker');
require('../../sass/main.scss');

function setDiscountAmountSymbol() {
    var value = $(this).val();
    var $amountAddon = $('#discount_discountCalculator_amount + .input-group-addon');

    if (/percent/i.test(value)) {
        $amountAddon.html('&#37;');
    } else {
        $amountAddon.html('&euro;');
    }
}

/**
 * Legacy jQuery datetimepicker setup, including the manual min/max bookkeeping that keeps the
 * two ends of the validity range consistent.
 *
 * @deprecated Superseded by `DateTimePickerType` and the Gui DateTimePicker, which handle range
 *   linking declaratively. Kept only for installations running spryker/gui older than 5.4.0.
 */
function initLegacyValidityPickers($from, $to, fromFormat, toFormat) {
    $from.datetimepicker({
        format: fromFormat,
        defaultTime: '00:00',
        todayButton: false,
        onShow: function () {
            if (!$to.val()) {
                return;
            }

            this.setOptions({
                maxDate: $to.datetimepicker('getValue'),
            });
        },
        onClose: function () {
            if (!$to.val()) {
                return;
            }

            var startDate = $from.datetimepicker('getValue');
            var endDate = $to.datetimepicker('getValue');
            if (startDate > endDate) {
                $to.datetimepicker({ value: startDate });
            }
        },
    });

    $to.datetimepicker({
        format: toFormat,
        defaultTime: '00:00',
        todayButton: false,
        onShow: function () {
            if (!$from.val()) {
                return;
            }

            this.setOptions({
                minDate: $from.datetimepicker('getValue'),
            });
        },
        onClose: function () {
            if (!$from.val()) {
                return;
            }

            var startDate = $from.datetimepicker('getValue');
            var endDate = $to.datetimepicker('getValue');
            if (startDate > endDate) {
                $from.datetimepicker({ value: endDate });
            }
        },
    });
}

$(document).ready(function () {
    var sqlCalculationBuilder = SqlFactory(
        '#discount_discountCalculator_collector_query_string',
        '#builder_calculation',
    );
    var sqlConditionBuilder = SqlFactory(
        '#discount_discountCondition_decision_rule_query_string',
        '#builder_condition',
        true,
    );
    var isQueryStringCollectorSelected = $('#discount_discountCalculator_collectorStrategyType_0').is(':checked');

    $('#create-discount-button').on('click', function (e) {
        e.preventDefault();

        $(this).prop('disabled', true).addClass('disabled');

        if (isQueryStringCollectorSelected) {
            sqlCalculationBuilder.saveQuery();
        }

        sqlConditionBuilder.saveQuery();
        const targets = document.querySelectorAll('.js-encode-on-change');
        for (const target of targets) {
            target.value = btoa(unescape(encodeURIComponent(target.value)));
        }

        $('#discount-form').submit();
    });

    $('#btn-calculation-get').on('click', function (event) {
        sqlCalculationBuilder.toggleButton(event);
    });

    $('#btn-condition-get').on('click', function (event) {
        sqlConditionBuilder.toggleButton(event);
    });

    setDiscountAmountSymbol.apply($('#discount_discountCalculator_calculator_plugin'));
    $('#discount_discountCalculator_calculator_plugin').on('change', setDiscountAmountSymbol);

    var $inputFrom = $('#discount_discountGeneral_valid_from');
    var $inputTo = $('#discount_discountGeneral_valid_to');
    var defaultDateFormat = 'd.m.Y H:i';
    var inputFromFormat = $inputFrom.data('format') || defaultDateFormat;
    var inputToFormat = $inputTo.data('format') || defaultDateFormat;

    // From spryker/gui 5.4.0 on, these fields are built with `DateTimePickerType`, which marks them
    // with `data-spryker-picker` and lets the Gui DateTimePicker initialize and range-link them.
    // Older Gui versions have no such type, so the legacy picker below is set up instead.
    if (!$inputFrom.is('[data-spryker-picker]')) {
        initLegacyValidityPickers($inputFrom, $inputTo, inputFromFormat, inputToFormat);
    }

    $('#discount_discountCalculator_collectorStrategyType input').each(function (index, element) {
        $('#collector-type-' + $(element).val()).hide();
        if ($(element).is(':checked')) {
            $('#collector-type-' + $(element).val()).show();
        }
    });

    $('#discount_discountCalculator_collectorStrategyType input').on('click', function (event) {
        $('#discount_discountCalculator_collectorStrategyType input').each(function (index, element) {
            $('#collector-type-' + $(element).val()).hide();
        });

        $('#collector-type-' + $(event.target).val()).show();
    });

    $('#discount_discountCalculator_calculator_plugin').on('change', function (event) {
        $('.discount-calculation-input-type').each(function (index, element) {
            $(element).hide();
        });

        var activeCalculatorInputType = $('#discount_discountCalculator_calculator_plugin :selected').data(
            'calculator-input-type',
        );
        $('#' + activeCalculatorInputType).show();
    });

    var activeCalculatorInputType = $('#discount_discountCalculator_calculator_plugin :selected').data(
        'calculator-input-type',
    );
    $('#' + activeCalculatorInputType).show();
});
