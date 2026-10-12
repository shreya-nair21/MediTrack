import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { AuthProvider, useAuth } from './context/AuthContext';
import Navbar from './components/Navbar';
import LoginScreen from './components/LoginScreen';
import DashboardScreen from './components/DashboardScreen';
import CaseListScreen from './components/CaseListScreen';
import CaseFormScreen from './components/CaseFormScreen';
import CaseDetailsScreen from './components/CaseDetailsScreen';

function MainRouter() {
    const { user } = useAuth();
    const [currentView, setCurrentView] = useState('dashboard');
    const [viewParams, setViewParams] = useState({});

    // Hash sync
    useEffect(() => {
        const handleHashChange = () => {
            const hash = window.location.hash.replace('#/', '');
            if (!hash || hash === 'dashboard') {
                setCurrentView('dashboard');
                setViewParams({});
            } else if (hash === 'cases') {
                setCurrentView('cases');
                setViewParams({});
            } else if (hash === 'cases/new' || hash === 'cases/create') {
                setCurrentView('cases-create');
                setViewParams({});
            } else if (hash.startsWith('cases/') && hash.endsWith('/edit')) {
                const parts = hash.split('/');
                setCurrentView('cases-edit');
                setViewParams({ id: parts[1] });
            } else if (hash.startsWith('cases/')) {
                const parts = hash.split('/');
                setCurrentView('cases-show');
                setViewParams({ id: parts[1] });
            } else if (hash === 'login') {
                setCurrentView('login');
                setViewParams({});
            }
        };

        window.addEventListener('hashchange', handleHashChange);
        handleHashChange();

        return () => window.removeEventListener('hashchange', handleHashChange);
    }, []);

    const navigate = (view, params = {}) => {
        setCurrentView(view);
        setViewParams(params);

        if (view === 'dashboard') {
            window.location.hash = '#/dashboard';
        } else if (view === 'cases') {
            window.location.hash = '#/cases';
        } else if (view === 'cases-create') {
            window.location.hash = '#/cases/new';
        } else if (view === 'cases-edit') {
            window.location.hash = `#/cases/${params.id}/edit`;
        } else if (view === 'cases-show') {
            window.location.hash = `#/cases/${params.id}`;
        } else if (view === 'login') {
            window.location.hash = '#/login';
        }
    };

    // If not authenticated, always show LoginScreen
    if (!user) {
        return (
            <main style={{ flex: 1, padding: '36px 0' }}>
                <div className="app-container">
                    <LoginScreen onLoginSuccess={() => navigate('dashboard')} />
                </div>
            </main>
        );
    }

    return (
        <div style={{ minHeight: '100vh', display: 'flex', flexDirection: 'column' }}>
            <Navbar currentView={currentView} navigate={navigate} />

            <main style={{ flex: 1, padding: '36px 0' }}>
                <div className="app-container">
                    {currentView === 'dashboard' && (
                        <DashboardScreen navigate={navigate} />
                    )}

                    {currentView === 'cases' && (
                        <CaseListScreen navigate={navigate} initialStatus={viewParams.status || ''} />
                    )}

                    {currentView === 'cases-create' && (
                        <CaseFormScreen navigate={navigate} />
                    )}

                    {currentView === 'cases-edit' && (
                        <CaseFormScreen caseId={viewParams.id} navigate={navigate} />
                    )}

                    {currentView === 'cases-show' && (
                        <CaseDetailsScreen caseId={viewParams.id} navigate={navigate} />
                    )}
                </div>
            </main>

            <footer className="footer-bar">
                <div className="app-container" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '12px' }}>
                    <div>
                        <strong>MediTrack</strong> &bull; Medical Implant Case Management System &copy; {new Date().getFullYear()} Jajal Medical.
                    </div>
                    <div style={{ fontSize: '12px' }}>
                        Institutional Medical Device Tracking Platform &bull; Built with React &amp; Coinbase Design System
                    </div>
                </div>
            </footer>
        </div>
    );
}

export default function App() {
    return (
        <AuthProvider>
            <MainRouter />
        </AuthProvider>
    );
}

const container = document.getElementById('react-app');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}
