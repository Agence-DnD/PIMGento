/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

TaskExecutor = Class.create();

TaskExecutor.prototype = {

    initialize: function(executeUrl, optionsUrl, tasks) {
        this.request    = null;
        this.console    = null;
        this.tasks      = tasks;
        this.executeUrl = executeUrl;
        this.optionsUrl = optionsUrl;
        this.button     = null;

        $('task-command').observe('change', function() {
            this.showExecute();
        }.bind(this));
    },

    run: function(filename, buttonId) {
        this.console = $('task-console');
        this.button  = $(buttonId);

        var command = $('task-command').value;

        if (command !== "") {
            this.console.update('').style.color = '#FF0';

            var options = $('task-options').serialize(true);
            var parameters = {};

            this.command(command, options, filename);

            parameters.options = JSON.stringify(options);
            parameters.command = command;
            parameters.file    = filename;
            parameters.step    = 0;

            this.launch(parameters);
        }
    },

    launch: function(parameters) {
        if (!this.request) {

            var taskClass = this;
            taskClass.addDisabled();

            this.request = new Ajax.Request(this.executeUrl, {
                method:'post',
                loaderArea:false,
                onSuccess: function(response) {
                    taskClass.request = null;

                    var data = response.responseText.evalJSON();

                    data.messages.each(function(message) {
                        if (message) {
                            taskClass.addLine(message, data.error);
                            data.error = false;
                        }
                    });

                    if (data.launch) {
                        parameters.task_id = data.task_id;
                        parameters.options = data.options;
                        parameters.step    = data.launch;

                        taskClass.launch(parameters);
                    } else {
                        taskClass.removeExecute();
                        taskClass.removeDisabled();
                    }
                },
                parameters: parameters
            });
        }
    },

    addLine: function(message, isError) {
        this.removeExecute();

        var li = new Element('li', { 'class': 'step-line execute'+(isError ? ' error' : '') }).update(message);
        this.console.insert({bottom:li});
        this.console.scrollTop = 9999;

        new Effect.Morph(li,{style:{color: '#0F0'},duration: 1});
    },

    removeExecute: function() {
        $$('.step-line').each(function(item) { item.removeClassName('execute'); });
    },

    addDisabled: function() {
        if (this.button) {
            this.button.setAttribute('disabled', 'disabled');
        }
    },

    removeDisabled: function() {
        if (this.button) {
            this.button.removeAttribute('disabled');
        }
    },

    showExecute: function() {
        var command = $('task-command').value;

        if(command) {
            var taskClass = this;

            Effect.Fade('task-execute-zone', {
                duration: 0.3,
                afterFinish: function () {
                    $$('.task-execute').each(function(param) { param.hide(); });

                    var type = taskClass.tasks[command].type;
                    $('type-' + type).show();

                    this.request = new Ajax.Request(taskClass.optionsUrl, {
                        loaderArea: false,
                        method: 'post',
                        onSuccess: function (response) {
                            taskClass.request = null;
                            $('task-options').update(response.responseText);
                            Effect.Appear('task-execute-zone',{duration:0.3});
                        },
                        parameters: {command: command}
                    });
                }
            });
        } else {
            Effect.Fade('task-execute-zone', {duration: 0.3});
        }
    },

    command: function(command, options, filename) {
        command = '# ' + command;
        if (filename) {
            command += '<br /># ' + filename;
        }
        $H(options).each(function(param) {
            command += '<br /># ' + param.key + ': ' + param.value;
        });
        this.addLine(command);
    }

};