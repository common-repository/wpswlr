import ReactDOM from 'react-dom/client';
import { Flip, ToastContainer } from 'react-toastify';
import { ErrorBoundary } from './components/ErrorBoundary';
import { App } from './App';
import './main.scss';

ReactDOM.createRoot(document.getElementById('wpswlr-r') as HTMLElement).render(
    <ErrorBoundary>
        <>
            <App />
            <ToastContainer
                draggable={false}
                transition={Flip}
            />
        </>
    </ErrorBoundary>
);
