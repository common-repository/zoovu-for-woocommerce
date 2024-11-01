import flatpickr from "flatpickr";
import $ from 'jquery';
import 'select2';
import pluck from "./helpers/pluck";

const forms = Array.from(document.querySelectorAll('.form-generator'));

/**
 * Form Generator Plugin
 *
 * @param form
 * @constructor
 */
const FormGenerator = (form) => {
    const fieldConts = Array.from(form.querySelectorAll('.field-cont'))
    const excludeField = document.getElementById('exclude_category_products')

    const initFieldFunctions = (fieldCont) => {
        const type = fieldCont.dataset.type
        const field = document.getElementById(fieldCont.dataset.field)

        switch (type) {
            case 'datetimepicker' :

                flatpickr(field, {
                    enableTime: true,
                    dateFormat: "Y-m-d H:i",
                    minDate: new Date()
                })

                break;

            case 'timepicker' :

                flatpickr(field, {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true
                })

                break;

            case 'autocomplete' :

                const ajaxRoute = fieldCont.dataset.ajax_route
                const autocompleteValue = JSON.parse(fieldCont.dataset.value)

                $(() => {

                    let selectParams = {
                        width: 350,
                        data: autocompleteValue
                    }

                    if (ajaxRoute) {
                        selectParams['ajax'] = {
                            url: function () {
                                if (field.name === 'exclude_category_products[]') {
                                    const values = jQuery('#individual_categories').select2('data')

                                    return ajaxRoute + '&term_ids=' + pluck(values, 'id').join(',')
                                } else {
                                    return ajaxRoute
                                }
                            },
                            dataType: 'json',
                            processResults: function (data) {
                                return {
                                    results: data.results
                                };
                            }
                        }
                    }

                    jQuery(field).select2(selectParams)
                    jQuery(field).val(pluck(autocompleteValue, 'id')).trigger('change')
                });

                break;
        }

        trackValues(field)
    }

    const triggerConditions = () => {
        fieldConts.forEach(function (fieldCont) {
            const conditions = fieldCont.dataset.conditions ? JSON.parse(fieldCont.dataset.conditions) : null

            if (! conditions) {
                return;
            }

            fieldConts.forEach(function (fieldContInner) {
                const fieldInner = document.getElementById(fieldContInner.dataset.field)

                if (! conditions[fieldInner.name]) {
                    return;
                }

                const fieldCondition = `'${fieldInner.value}' ${conditions[fieldInner.name]['condition']} '${conditions[fieldInner.name]['value']}'`

                if (eval(fieldCondition)) {
                    fieldCont.style.display = 'table-row'
                } else {
                    fieldCont.style.display = 'none'
                }
            })
        })
    }

    const trackValues = (field) => {
        jQuery(field).on('change', function () {
            triggerConditions()

            if (field.name === 'individual_categories') {
                 jQuery('#exclude_category_products').val(null).trigger('change')
            }
        })
    }

    fieldConts.forEach(function (fieldCont) {
        initFieldFunctions(fieldCont)
    })

    triggerConditions();
}

/**
 * Inject form generator for all FG forms
 */
forms.forEach(function (form) {
    FormGenerator(form)
})
