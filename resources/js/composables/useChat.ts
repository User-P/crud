import { ChatMessage } from "@/interfaces/chat-message.interface"
import { ref } from "vue"

export const useChat = () => {

    const messages = ref<ChatMessage[]>([{
        id: new Date().getTime(),
        message: 'hola mundo',
        itsMine: true
    }, {
        id: new Date().getTime(),
        message: 'hola mundo',
        itsMine: false
    }, {
        id: new Date().getTime(),
        message: 'hola mundo',
        itsMine: true
    }])

    const onMessage = (text: string) => {
        messages.value.push({
            id: new Date().getTime(),
            itsMine: true,
            message: text
        })
    }

    return {
        messages,
        onMessage

    }
}
