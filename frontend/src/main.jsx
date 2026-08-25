import React, { useEffect, useMemo, useState } from 'react'
import { createRoot } from 'react-dom/client'
import Pusher from 'pusher-js'
import './styles.css'

const config = {
  key: import.meta.env.VITE_PUSHER_KEY || 'mqtt-test-key',
  host: import.meta.env.VITE_PUSHER_HOST || window.location.hostname,
  port: Number(import.meta.env.VITE_PUSHER_PORT || 6001),
  channel: import.meta.env.VITE_PUSHER_CHANNEL || 'mqtt.telemetry',
  event: import.meta.env.VITE_PUSHER_EVENT || 'telemetry.updated',
}

function Gauge({ value }) {
  const min = 20
  const max = 30
  const angle = -135 + ((value - min) / (max - min)) * 270
  const ticks = useMemo(() => Array.from({ length: 11 }, (_, i) => min + i), [])

  return (
    <div className="gauge" aria-label={`مقدار فعلی ${value}`}>
      <div className="gauge-ring">
        {ticks.map((tick) => {
          const tickAngle = -135 + ((tick - min) / (max - min)) * 270
          return (
            <span
              className="tick"
              key={tick}
              style={{ transform: `translate(-50%, -50%) rotate(${tickAngle}deg) translateY(-126px)` }}
            >
              {tick}
            </span>
          )
        })}
        <div className="needle" style={{ transform: `rotate(${angle}deg)` }} />
        <div className="hub" />
      </div>
      <strong className="value">{value}</strong>
    </div>
  )
}

function App() {
  const [value, setValue] = useState(25)
  const [connected, setConnected] = useState(false)
  const [lastMessage, setLastMessage] = useState(null)
  const [error, setError] = useState('')

  useEffect(() => {
    const pusher = new Pusher(config.key, {
      wsHost: config.host,
      wsPort: config.port,
      wssPort: config.port,
      forceTLS: false,
      enabledTransports: ['ws'],
      cluster: 'mt1',
    })

    const connection = pusher.connection
    const onStateChange = ({ current }) => setConnected(current === 'connected')
    const onError = (err) => setError(err?.error?.data?.message || 'خطای اتصال WebSocket')

    connection.bind('state_change', onStateChange)
    connection.bind('error', onError)

    const channel = pusher.subscribe(config.channel)
    channel.bind(config.event, (payload) => {
      const next = Number(payload?.value)
      if (Number.isFinite(next)) {
        setValue(Math.min(30, Math.max(20, next)))
        setLastMessage(payload)
        setError('')
      }
    })

    return () => {
      channel.unbind_all()
      pusher.unsubscribe(config.channel)
      connection.unbind('state_change', onStateChange)
      connection.unbind('error', onError)
      pusher.disconnect()
    }
  }, [])

  return (
    <main className="page">
      <section className="card">
        <p className="eyebrow">MQTT • REALTIME</p>
        <h1>نمایش لحظه‌ای داده‌های MQTT</h1>
        <p className="description">
          Publisher هر یک ثانیه مقدار تصادفی بین ۲۰ تا ۳۰ را ارسال می‌کند و Subscriber آن را از MQTT گرفته و از طریق Soketi به این نمودار می‌فرستد.
        </p>

        <div className={`status ${connected ? 'ok' : 'offline'}`}>
          <span /> {connected ? 'متصل به Soketi' : 'در انتظار اتصال'}
        </div>

        <Gauge value={value} />

        <div className="details">
          <div><span>Topic</span><strong>test/your_name</strong></div>
          <div><span>Event</span><strong>{config.event}</strong></div>
          <div><span>آخرین پیام</span><strong>{lastMessage?.timestamp || '—'}</strong></div>
        </div>

        {error && <p className="error">{error}</p>}
      </section>
    </main>
  )
}

createRoot(document.getElementById('root')).render(<App />)
