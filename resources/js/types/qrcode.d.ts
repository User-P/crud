declare module 'qrcode' {
    export interface QRCodeToDataURLOptions {
        width?: number
        margin?: number
        scale?: number
        color?: {
            dark?: string
            light?: string
        }
    }

    export function toDataURL(text: string, options?: QRCodeToDataURLOptions | string): Promise<string>

    const QRCode: {
        toDataURL: typeof toDataURL
    }

    export default QRCode
}
