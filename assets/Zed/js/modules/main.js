/**
 * Copyright (c) 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

require('ZedGui');
var SqlFactory = require('./libs/sql-factory');
var DiscountNavigation = require('./libs/navigation');

require('../../sass/main.scss');

$(document).ready(function(){

    new DiscountNavigation();

    var sqlCalculationBuilder = SqlFactory('#discount_discountCalculator_collector_query_string', '#builder_calculation');
    var sqlConditionBuilder = SqlFactory('#discount_discountCondition_decision_rule_query_string', '#builder_condition', true);

    $('#create-discount-button').on('click', function(element) {
        element.preventDefault();
        sqlCalculationBuilder.saveQuery();
        sqlConditionBuilder.saveQuery();

        $('#discount-form').submit();
    });

    $('#btn-calculation-get').on('click', function(event){
        sqlCalculationBuilder.toggleButton(event);
    });

    $('#btn-condition-get').on('click', function(event){
        sqlConditionBuilder.toggleButton(event);
    });


    $('#discount_discountCalculator_calculator_plugin').on('change', function(){
        var value = $(this).val();
        var $amountAddon = $('#discount_discountCalculator_amount + .input-group-addon');

        if (/percent/i.test(value)) {
            $amountAddon.html('&#37;');
        } else {
            $amountAddon.html('&euro;');
        }
    });

    $('#discount_discountGeneral_valid_from').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        numberOfMonths: 3,
        defaultData: 0,
        onClose: function(selectedDate){
            $('#discount_discountGeneral_valid_to').datepicker('option', 'minDate', selectedDate);
        }
    });

    $('#discount_discountGeneral_valid_to').datepicker({
        defaultData: 0,
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        numberOfMonths: 3,
        onClose: function(selectedDate){
            $('#discount_discountGeneral_valid_from').datepicker('option', 'maxDate', selectedDate);
        }
    });
});
