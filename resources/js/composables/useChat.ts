import { sleep } from "@/helpers/sleep"
import { ChatMessage } from "@/interfaces/chat-message.interface"
import { YesNoResponse } from "@/interfaces/yesy-no-response"
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

    const getResponse = async () => {
        const resp = await fetch('https://yesno.wtf/api')
        const data = await resp.json() as YesNoResponse

        return data

    }

    const onMessage = async (text: string) => {
        if (text.length === 0) return

        messages.value.push({
            id: new Date().getTime(),
            itsMine: true,
            message: text
        })
        if (!text.endsWith('?')) return

        await sleep(1)
        const {answer, image} = await getResponse()

        messages.value.push({
            id: new Date().getTime(),
            itsMine: false,
            message: answer,
            image
        })
    }

    return {
        messages,
        onMessage

    }
}
