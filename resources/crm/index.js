import { createRoot } from 'react-dom/client';
import App from './src/components/pages/app';

const container = document.getElementById('app');
const root = createRoot(container);
root.render(<App />);
