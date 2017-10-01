function toggleCollapse(sender) {
    var box = $(sender).parents(".box").first();
    //Find the body and the footer
    var boxBody = box.find(".box-body").first();
    if (!box.hasClass("collapsed-box")) {
        box.addClass("collapsed-box");
        boxBody.slideUp();
        $(sender).find("i.fa-minus").first().addClass("fa-plus").removeClass("fa-minus");
    } else {
        box.removeClass("collapsed-box");
        boxBody.slideDown();
        $(sender).find("i.fa-plus").first().addClass("fa-minus").removeClass("fa-plus");
    }
}

var processId = 1500;
function createProcess(text) {
    var newProcess = { id: processId++, name: text };
    return bot.processes.push(newProcess);
}

var parameterId = 2500;
function createParameter(text, processId) {
    processId = parseInt(processId);
    var process = bot.processes.filter(function (pr) {
        return pr.id === processId;
    })[0];
    var parameter = { id: parameterId++, name: text }
    process.parameters.push(parameter);
    return parameter;
}

var botMessageId = 3500;
function createBotMessage(text) {
    return { id: botMessageId++, text: text };
}

var userMessageOptionId = 4500;
function createUserMessageOption(text) {
    return { id: userMessageOptionId++, name: text };
}

function onEdit(sender, event, tmpl, selector, func, processId) {
    if (event.keyCode == 13) {
        var template = $.templates(tmpl);
        var val = $(sender).val();
        if (func)
            val = func(val, processId);

        var htmlOutput = template.render(val, { processId: processId });
        $(selector).append(htmlOutput);
        $(sender).val("");
    }
};

function configureProcess(processId) {
    var template = $.templates("#processSettingsTmpl");
    var process = bot.processes.filter(function (pr) {
        return pr.id === processId;
    })[0];
    var htmlOutput = template.render(process);

    $("#processSettings").html(htmlOutput);
}

function configureParameter(processId, parameterId) {
    var template = $.templates("#parameterSettingsTmpl");
    var process = bot.processes.filter(function (pr) {
        return pr.id === processId;
    })[0];
    var parameter = process.parameters.filter(function (par) {
        return par.id === parameterId;
    });
    var htmlOutput = template.render(parameter);

    $("#parameterSettings").html(htmlOutput);
}

function onParameterTypeChange(sender, event) {
    var val = $(sender).val();
    if (val == "List") {
        $("#parameterListSettings").show();
    } else {
        $("#parameterListSettings").hide();
    }
}

function onUserOptionIsFinalChanged(sender, event, userOptionId, paramType) {
    var keywordsSelector = "#userMessageOption_" + userOptionId + "_keywords_container";
    var botMessagesSelector = "#userMessageOption_" + userOptionId + "_botMessages_container";

    var keywordsTemplate = $.templates("#userMessageOptionKeywordsTmpl");
    var botMessagesTemplate = $.templates("#userMessageOptionBotAnswersTmpl");

    var userOption = pageData["UserMessageOptions"][userOptionId];
    userOption.isFinal = sender.checked;
    $(keywordsSelector).html(keywordsTemplate.render(userOption, { type: paramType }));
    $(botMessagesSelector).html(botMessagesTemplate.render(userOption, { type: paramType }));
}

var bot = {
    processes: [
        {
            "id": 1001,
            "name": "Криптовалюта в России",
            "keywords": [
                "Криптовалюта, валюта",
                "Россия, РФ, Российская Федерация"
            ],
            "mandatory_params": [2001, 2002],
            "parameters": [
                {
                    "id": 2001,
                    "name": "Перспективность использования технологии блокчейн",
                    "type": "List",
                    "options": "1,2,3,4,5,6,7,8,9,10",
                    "botMessagesSequence": [
                        {
                            "id": 3001,
                            "text": "Оцените перспективность использования технологии блокчейн для финансового рынка РФ",
                            "isFinal": false,
                            "userMessagesOptions": [
                                {
                                    "id": 4001,
                                    "name": "Ответ дан",
                                    "keywords": [
                                        [
                                            "да",
                                            "конечно",
                                            "выбрал"
                                        ]
                                    ],
                                    "botMessagesSequence": [
                                        {
                                            "id": 3002,
                                            "text": "Какой?",
                                            "userMessagesOptions": [
                                                {
                                                    "id": 4002,
                                                    "name": "Известный тариф",
                                                    "isFinal": true,
                                                    "keywords": null
                                                },
                                                {
                                                    "id": 4003,
                                                    "name": "Не помню",
                                                    "keywords": [
                                                        "не помню, не знаю"
                                                    ],
                                                    "botMessagesSequence": {
                                                        "referenceId": 4011
                                                    }
                                                },
                                                {
                                                    "id": 4004,
                                                    "name": "Неизвестный тариф",
                                                    "isWildMatch": true,
                                                    "keywords": null,
                                                    "botMessagesSequence": [
                                                        {
                                                            "id": 3003,
                                                            "text":
                                                                "Я не такой умный бот на самом деле, не могу Вас понять. Выберите тариф из списка,пожалуйста : Все за 100, Все за 300, Все за 500, Все бесплатно ",
                                                            "userMessageOptions": {
                                                                "referenceId": 3002
                                                            }
                                                        }
                                                    ]
                                                }
                                            ]
                                        }
                                    ]
                                },
                                {
                                    "id": 4011,
                                    "name": "Нет, тариф не выбран",
                                    "keywords": "нет, не выбрал, не знаю",
                                    "botMessagesSequence": [
                                        {
                                            "id": 3011,
                                            "name": "Вам нужна помощь в выборе тарифа?",
                                            "userMessagesOptions": [
                                                {
                                                    "name": "Да, нужна помощь в выборе тарифа",
                                                    "keywords": [
                                                        "да, нужна, помогите, помощь, пожалуйста"
                                                    ],
                                                    "isCancel": true,
                                                    "botMessage":
                                                        "К сожалению, я Вам не могу помочь, я не такой умный... ("
                                                },
                                                {
                                                    "name": "Нет, помощь в выборе тарифа не нужна",
                                                    "keywords": [
                                                        "да, нужна, помогите, помощь, пожалуйста"
                                                    ],
                                                    "isCancel": true,
                                                    "botMessage":
                                                        "Ок! Тогда возвращайтесь, когда выберете тариф. А вообще, Вы странный...)"
                                                }
                                            ]
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                },
                {
                    "id": 2101,
                    "name": "Полезность официального признания криптовалют",
                    "type": "List",
                    "options": "с начала месяца",
                    "botMessagesSequence": [
                        {
                            "id": 3101,
                            "text": "Оцените полезность официального признания криптовалют для российской экономики",
                            "isFinal": true,
                            "userMessagesOptions": [
                                {
                                    id: 4101,
                                    name: "Правильная дата",
                                    isFinal: true
                                }
                            ]
                        }
                    ]
                }
            ],
            "actions":
            [
                {
                    id: 5001,
                    name: "Успешная смена тарифа",
                    text: "Вы выбрали тариф: {} и дату: {}"
                },
                {
                    id: 5002,
                    name: "Непоняточка",
                    text: "Пожалуйста подождтие. С вами свяжется СПЕЦИАЛЬНО обученный ЧЕЛОВЕК. НЕ БОТ!"
                }
            ]
        },
        {
            name: "Не прошел платеж",
            id: 1002
        },
        {
            name: "Непонятны списания со счета",
            id: 1003
        },
        {
            name: "Помочь в выборе тарифа",
            id: 1004
        },
        {
            name: "Узнать стоимость услуги",
            id: 1005
        }
    ]
};

$(document).ready(function () {
    var template = $.templates("#processTmpl");

    var htmlOutput = template.render(bot.processes);

    $("#processesList").append(htmlOutput);
});

var pageData = {};

$.views.tags({
    // Tag with render method to reverse-sort an array
    setPageData: function () {
        var table = this.tagCtx.props.table;
        var key = this.tagCtx.props.key;
        var value = this.tagCtx.props.value;

        if (!pageData[table])
            pageData[table] = {};

        if (value)
            pageData[table][key] = value;
        else
            pageData[table][key] = this.tagCtx.view.data;

        return "";
    }
});