'use strict';

window.SQLParser = require('@spryker/sql-parser-mistic/browser/sql-parser');
require('@spryker/jquery-query-builder');

const LIST_VALUE_DELIMITER = ';';
const LIST_OPERATORS = ['in', 'not_in'];
const FREE_TEXT_OPERATORS = ['contains', 'not_contains'];
const SUGGEST_MODE_DATA_KEY = 'suggest-mode';
const SUGGEST_MODE_LIST = 'list';
const SUGGEST_MODE_EXACT = 'exact';
const SUGGEST_MODE_FREE_TEXT = 'free-text';
const SUGGEST_MINIMUM_INPUT_LENGTH = 2;
const SUGGEST_REQUEST_DELAY_MILLISECONDS = 250;
const FIELD_SELECT_CLASS = 'form-control';

function SprykerQueryBuilder(options) {
    var self = this;
    this.builder = null;
    this.displayQueryBuilder = true;
    this.getFiltersUrl = options.ajaxUrl;
    this.sql = options.sqlQuery;
    this.inputElement = options.inputElement;
    this.targetElement = options.targetElement;
    this.label = options.label || 'Build Query';
    this.init = function () {
        self.builder = $(self.targetElement);
        self.createBuilder();
    };

    this.init();

    if (options.disableValidation === true) {
        self.builder.on('validationError.queryBuilder', function (e, rule, error, value) {
            e.preventDefault();
        });
    }
}

SprykerQueryBuilder.prototype.createBuilder = function () {
    var self = this;

    $.get(self.getFiltersUrl).done(function (filters) {
        filters.forEach(function (filter) {
            if (self.isSuggestFilter(filter)) {
                self.addSuggestPluginConfig(filter);
                self.addSuggestValueHandlers(filter);
            }
        });

        self.builder.off('.sprykerSuggest');
        self.builder.on(
            'afterCreateRuleInput.queryBuilder.sprykerSuggest afterUpdateRuleOperator.queryBuilder.sprykerSuggest',
            function (event, rule) {
                self.applySuggestInputMode(rule);
                self.applySuggestContainerClass(rule);
            },
        );

        self.builder.queryBuilder({
            filters: filters,
            sqlOperators: self.getSqlOperators(),
            sqlRuleOperator: self.getSqlRuleOperators(),
            allow_empty: true,
            display_empty_filter: true,
        });
        self.builder.prepend('<label class="control-label query-builder-label">' + self.label + '</label>');
        if (typeof self.sql !== 'undefined' && self.sql !== '') {
            self.builder.queryBuilder('setRulesFromSQL', self.sql);
        }
    });
};

SprykerQueryBuilder.prototype.isSuggestFilter = function (filter) {
    return !!(filter && filter.suggest_url);
};

SprykerQueryBuilder.prototype.addSuggestPluginConfig = function (filter) {
    filter.plugin_config = {
        ajax: {
            url: filter.suggest_url,
            delay: SUGGEST_REQUEST_DELAY_MILLISECONDS,
        },
        minimumInputLength: SUGGEST_MINIMUM_INPUT_LENGTH,
        tags: false,
        width: 'off',
    };
};

SprykerQueryBuilder.prototype.isListOperator = function (rule) {
    return !!rule.operator && LIST_OPERATORS.indexOf(rule.operator.type) !== -1;
};

SprykerQueryBuilder.prototype.getSuggestSelect = function (rule) {
    return rule.$el.find('.rule-value-container select');
};

SprykerQueryBuilder.prototype.addSuggestValueHandlers = function (filter) {
    var self = this;

    filter.valueGetter = function (rule) {
        var value = self.getSuggestSelect(rule).val();

        if (Array.isArray(value)) {
            value = value.join(LIST_VALUE_DELIMITER);
        }

        return value ? value : undefined;
    };

    filter.valueSetter = function (rule, value) {
        var $select = self.getSuggestSelect(rule);
        var values = self.splitSuggestValue(rule, value);

        values.forEach(function (singleValue) {
            if (!self.hasOption($select, singleValue)) {
                $select.append(new Option(singleValue, singleValue, true, true));
            }
        });

        $select.val(self.isListOperator(rule) ? values : values[0]).trigger('change');
    };
};

SprykerQueryBuilder.prototype.splitSuggestValue = function (rule, value) {
    if (value === undefined || value === null || value === '') {
        return [];
    }

    if (!this.isListOperator(rule)) {
        return [String(value)];
    }

    return String(value)
        .split(LIST_VALUE_DELIMITER)
        .map(function (singleValue) {
            return singleValue.trim();
        })
        .filter(function (singleValue) {
            return singleValue !== '';
        });
};

SprykerQueryBuilder.prototype.hasOption = function ($select, value) {
    return Array.from($select.get(0).options).some(function (option) {
        return option.value === value;
    });
};

SprykerQueryBuilder.prototype.isFreeTextOperator = function (rule) {
    return !!rule.operator && FREE_TEXT_OPERATORS.indexOf(rule.operator.type) !== -1;
};

SprykerQueryBuilder.prototype.getSuggestMode = function (rule) {
    if (this.isListOperator(rule)) {
        return SUGGEST_MODE_LIST;
    }

    if (this.isFreeTextOperator(rule)) {
        return SUGGEST_MODE_FREE_TEXT;
    }

    return SUGGEST_MODE_EXACT;
};

SprykerQueryBuilder.prototype.getCurrentSuggestMode = function ($select) {
    var currentMode = $select.data(SUGGEST_MODE_DATA_KEY);

    if (currentMode) {
        return currentMode;
    }

    return $select.prop('multiple') ? SUGGEST_MODE_LIST : SUGGEST_MODE_EXACT;
};

SprykerQueryBuilder.prototype.applySuggestInputMode = function (rule) {
    if (!rule.filter || !this.isSuggestFilter(rule.filter)) {
        return;
    }

    var $select = this.getSuggestSelect(rule);
    var mode = this.getSuggestMode(rule);

    if (this.getCurrentSuggestMode($select) === mode) {
        return;
    }

    var isMultiple = mode === SUGGEST_MODE_LIST;
    var selectedValues = [].concat($select.val() || []);
    var keptValues = isMultiple ? selectedValues : selectedValues.slice(0, 1);

    if ($select.data('select2')) {
        $select.select2('destroy');
    }

    $select.empty().prop('multiple', isMultiple);
    keptValues.forEach(function (value) {
        $select.append(new Option(value, value, true, true));
    });
    $select.data(SUGGEST_MODE_DATA_KEY, mode);
    $select.select2($.extend({}, rule.filter.plugin_config, { tags: mode === SUGGEST_MODE_FREE_TEXT }));
    $select.trigger('change');
};

SprykerQueryBuilder.prototype.applySuggestContainerClass = function (rule) {
    if (!rule.filter || !this.isSuggestFilter(rule.filter)) {
        return;
    }

    this.getSuggestSelect(rule).next('.select2-container').addClass(FIELD_SELECT_CLASS);
};

SprykerQueryBuilder.prototype.toggleButton = function (event) {
    var self = this;
    var inputElementContainer = $(self.inputElement).parent();
    var label = '';
    var button = $(event.target);

    if (!!self.displayQueryBuilder) {
        self.saveQuery();
        inputElementContainer.removeClass('hidden');
        self.builder.addClass('hidden');
        self.builder.queryBuilder('destroy');
        self.displayQueryBuilder = false;
        self.builder.children('.query-builder-label').remove();
        label = button.data('label-query-builder');
    } else {
        inputElementContainer.addClass('hidden');
        self.builder.removeClass('hidden');
        self.displayQueryBuilder = true;
        self.sql = $(self.inputElement).val();
        self.createBuilder();
        label = button.data('label-plain-query');
    }
    button.text(label);
};

SprykerQueryBuilder.prototype.getSqlOperators = function () {
    return {
        contains: {
            op: 'CONTAINS ?',
            mod: '{0}',
        },
        not_contains: {
            op: 'DOES NOT CONTAIN ?',
            mod: '{0}',
        },
        in: {
            op: 'IS IN ?',
            sep: ', ',
        },
        not_in: {
            op: 'IS NOT IN ?',
            sep: ', ',
        },
    };
};

SprykerQueryBuilder.prototype.getSqlRuleOperators = function () {
    return {
        CONTAINS: function (v) {
            return {
                val: v,
                op: 'contains',
            };
        },
        'DOES NOT CONTAIN': function (v) {
            return {
                val: v,
                op: 'not_contains',
            };
        },
        'IS IN': function (v) {
            return {
                val: v,
                op: 'in',
            };
        },
        'IS NOT IN': function (v) {
            return {
                val: v,
                op: 'not_in',
            };
        },
    };
};

SprykerQueryBuilder.prototype.saveQuery = function () {
    if (!this.inputElement.parent().hasClass('hidden')) {
        return;
    }

    var status = this.builder.queryBuilder('getRules') || {};

    if (!status.rules || !status.rules.length) {
        return this.inputElement.val('');
    }

    var result = this.builder.queryBuilder('getSQL', false);
    if (result !== '') {
        this.builder.queryBuilder('validate');
    }
    this.inputElement.val(result.sql);
};

module.exports = SprykerQueryBuilder;
