import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { Provider } from 'react-redux'
import { Toaster } from 'sonner'
import './index.css'
import App from './App.tsx'
import { store } from './store'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <Provider store={store}>
      <App />
      {/* Shows the toast(...) messages, e.g. after a quick edit in a table */}
      <Toaster theme="dark" position="bottom-right" richColors />
    </Provider>
  </StrictMode>,
)
