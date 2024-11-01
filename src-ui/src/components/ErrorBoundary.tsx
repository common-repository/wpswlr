import React from 'react';
import { LoaderService } from '../services/LoaderService';
import { tr } from '../i18n';
import style from './ErrorBoundary.module.scss';

type Props = React.PropsWithChildren;

interface State {
    hasError: boolean;
    loading: boolean;
}

export class ErrorBoundary extends React.Component<Props, State> {
    constructor(props: Props) {
        super(props);
        this.state = {
            hasError: false,
            loading: true,
        };
    }

    static getDerivedStateFromError() {
        return { hasError: true, loading: true };
    }

    componentDidCatch() {
        LoaderService.hide();
        setTimeout(() => {
            this.setState((prev) => ({ ...prev, loading: false }));
        }, 1000);
    }

    render() {
        const { hasError, loading } = this.state;
        const { children } = this.props;
        if (!hasError) {
            return children;
        }

        function handleReload() {
            window.location.reload();
        }

        return (
            <div className={loading ? style.loading : ''}>
                <div className={style.h1}>{tr.app.errorBoundaryHeader}</div>
                <div className={style.h2}>
                    {tr.app.errorBoundaryText1}&nbsp;
                    <button
                        type="button"
                        onClick={handleReload}
                        className="button-link"
                    >
                        {tr.app.errorBoundaryText2}
                    </button>
                    .
                </div>
                <div>
                    <div className={`${style.gear} ${style.one}`}>
                        <div className={style.bar}> </div>
                        <div className={style.bar}> </div>
                        <div className={style.bar}> </div>
                    </div>
                    <div className={`${style.gear} ${style.two}`}>
                        <div className={style.bar}> </div>
                        <div className={style.bar}> </div>
                        <div className={style.bar}> </div>
                    </div>
                    <div className={`${style.gear} ${style.three}`}>
                        <div className={style.bar}> </div>
                        <div className={style.bar}> </div>
                        <div className={style.bar}> </div>
                    </div>
                </div>
            </div>
        );
    }
}
