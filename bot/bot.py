# -*- coding: utf-8 -*-
import telebot
import config
import json
from sqlalchemy import desc
from datetime import datetime
from dbms.db_model import session, SessionModel, ProcessModel

bot = telebot.TeleBot(config.token)


@bot.message_handler(content_types=["text"])
def process_all_messages(message):
    user_id = message.from_user.id
    db_model, is_new = get_current_process_and_step(user_id)
    process = None
    answers = None
    if db_model.current_process is None:
        process = get_new_process( message)
        if process is None:
            pass
        else:
            setattr(db_model, 'current_process', process['id'])
    else:
        process = get_current_process(db_model.current_process)
    if not is_new:
        result_message = 'Привет. Я начинающий бот-помощник\n'
    else:
        result_message = ''

    if db_model.process_params is None:
        answers = dict()
    else:
        answers = json.loads(db_model.process_params)

    result_message = get_result_message_by_process(answers, message, process, db_model, result_message)
    bot.reply_to(message, result_message)


def get_result_message_by_process(answers, message, process, session_model, result_message):
    if (process is None):
        if ('?' in message.text):
            result_message += 'Скорее всего я не понял ваш вопрос =( \n Соединяю с компетентным представителем тех. поддержки'
            setattr(session_model, 'expired', True)
        else:
            result_message += 'Скорее задавай свой вопрос!'
        return result_message
    mandatory_params = process['mandatory_params']
    for param in process['parameters']:
        options = param['options']
        opt_intersection = find_intersection_string(options, message.text)

        if opt_intersection:
            # идем к следующему параметру
            answers[str(param['id'])] = opt_intersection

            setattr(session_model, 'process_params', json.dumps(answers))
            session.commit()
            mand_keys = list(answers.keys())
            if len(mand_keys) == len(mandatory_params):
                action_msg = process['actions'][0]['text']
                for k in answers.keys():
                    action_msg = action_msg.replace(
                        "{" + k + "}", answers[k])
                setattr(session_model, 'expired', 1)
                session.commit()
                return action_msg
        elif str(param['id']) in answers.keys():
            pass
        else:
            result_message += param['botMessagesSequence'][0]['text']
            return result_message
    return '!!!Exceptional case!!!'


def _get_keys(list_of_dict):
    keys = list()
    for item in list_of_dict:
        keys.extend(item.keys())
    return keys


def get_new_process(message):
    processes = session.query(ProcessModel).all()
    for p in processes:
        has_intersection = is_new_process_has_intersection(message, p)
        if has_intersection:
            return p.process_flow
    return None


def get_current_process(process_id):
    record = session.query(ProcessModel).get(process_id)
    if record:
        return record.process_flow
    return None


def is_new_process_has_intersection(message, p):
    json_data = p.process_flow
    keywords = json_data['keywords']
    has_intersection = find_intersection(keywords, message.text)
    return has_intersection


def find_intersection(keywords, words):
    for k_array in keywords:
        k_set = set(k_array.split(','))
        if not (is_keyword_contains_value(k_set, words)):
            return False
    return True


def is_keyword_contains_value(k_set, words):
    for w in k_set:
        if (w in words):
            return True
    return False


def find_intersection_string(keywords, words):
    k_set = keywords.split(',')
    for w in k_set:
        if w in words:
            return w
    return None


def get_current_process_and_step(user_id):
    # find session, return new if not one found
    query_result = session.query(SessionModel).filter(SessionModel.user_id == user_id,
                                                      SessionModel.expired == 0)
    record = query_result.order_by(desc(SessionModel.session_id)).first()
    if record is None:
        model = SessionModel(user_id=user_id, last_access_time=datetime.now())
        session.add(model)
        session.commit()
        return model, False
    else:
        return record, True


if __name__ == '__main__':
    bot.polling(none_stop=True)
