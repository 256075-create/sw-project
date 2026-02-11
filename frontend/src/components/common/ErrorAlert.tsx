interface ErrorAlertProps {
  message: string;
  onDismiss?: () => void;
}

export default function ErrorAlert({ message, onDismiss }: ErrorAlertProps) {
  return (
    <div className="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
      <div className="flex items-center justify-between">
        <span>{message}</span>
        {onDismiss && (
          <button onClick={onDismiss} className="ml-4 text-red-500 hover:text-red-700">
            &times;
          </button>
        )}
      </div>
    </div>
  );
}
