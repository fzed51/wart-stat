import React, { type ReactNode } from 'react';
import { Alert } from '../Alert';
import { Button } from '../Button';
import { Card } from '../Card';
import './ErrorBoundary.css';

interface Props {
  children: ReactNode;
}

interface State {
  hasError: boolean;
  error: Error | null;
  errorInfo: { componentStack: string } | null;
}

export class ErrorBoundary extends React.Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = {
      hasError: false,
      error: null,
      errorInfo: null,
    };
  }

  static getDerivedStateFromError(_error: Error): Partial<State> {
    console.error('ErrorBoundary caught an error:', _error);
    return { hasError: true };
  }

  componentDidCatch(error: Error, errorInfo: { componentStack: string }) {
    this.setState({
      error,
      errorInfo,
    });
    console.error('Error caught by ErrorBoundary:', error, errorInfo);
  }

  handleReset = () => {
    this.setState({
      hasError: false,
      error: null,
      errorInfo: null,
    });
  };

  render() {
    if (this.state.hasError) {
      return (
        <div className="error-boundary">
          <Card className="error-boundary__card" title="Erreur applicative">
            <Alert variant="error" className="error-boundary__alert">
              Une erreur est survenue dans l'application. Veuillez réessayer ou contacter le support.
            </Alert>

            {import.meta.env.MODE === 'development' && this.state.error && (
              <details className="error-boundary__details">
                <summary className="error-boundary__summary">
                  Détails techniques (développement uniquement)
                </summary>
                <pre className="error-boundary__stack">
                  {this.state.error.toString()}
                  {this.state.errorInfo?.componentStack}
                </pre>
              </details>
            )}

            <div className="error-boundary__actions">
              <Button
                variant="primary"
                onClick={this.handleReset}
                className="error-boundary__action-btn"
              >
                Réessayer
              </Button>
              <Button
                variant="ghost"
                onClick={() => window.location.href = '/'}
                className="error-boundary__action-btn"
              >
                Accueil
              </Button>
            </div>
          </Card>
        </div>
      );
    }

    return this.props.children;
  }
}
