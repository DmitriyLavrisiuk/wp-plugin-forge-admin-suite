type ErrorStateProps = {
  message: string;
  onRetry?: () => void;
};

function ErrorState({ message, onRetry }: ErrorStateProps) {
  return (
    <div className="space-y-3 text-sm text-red-600">
      <p>{message}</p>
      {onRetry && (
        <button
          type="button"
          onClick={onRetry}
          className="rounded-md border border-red-200 bg-white px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50"
        >
          Retry
        </button>
      )}
    </div>
  );
}

export default ErrorState;
