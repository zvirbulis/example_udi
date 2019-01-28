//require("./utils/socket.io");
const Echo = require("laravel-echo");

if (window.app_env.enable_messenger) {
    let defaultPort = process.env.MIX_LARAVEL_ECHO_SERVER_PORT;
    defaultPort = defaultPort ? defaultPort : 6001;

    window.Echo = new Echo({
        broadcaster: 'socket.io',
        host: window.location.hostname + ':' + defaultPort
    });

    if (window.userId) {
        window.Echo.private('chat.user.' + window.userId)
            .listen('NewChatMessage', (data) => {
                if (data.message.user_id != window.userId) {
                    app.__vue__.$notify({
                        title: 'Вам пришло сообщение',
                        text: data.message.body,
                        type: 'success'
                    });

                    app.__vue__.$store.commit('setCountNewMessages', true);
                }

                app.__vue__.$root.$emit('NewChatMessage', data.chatData);
            });
    }
} else {
    console.log('skip broadcasting');
}

