import { useState } from 'react';
import type { FormEvent } from 'react';
import { useLoginMutation } from '../../client/authApi';
import { getErrorMessage, getFieldErrors, isApiError } from '../../errors/apiError';
import FormField from '../SharedComponents/FormField';

/**
 * Email + password form. On success "me" is refetched and the login page
 * redirects, so this form does not navigate itself.
 */
export default function LoginForm() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [login, { isLoading, error }] = useLoginMutation();

  const fieldErrors = getFieldErrors(error);
  // 422 messages are shown under the fields; anything else (429, network) above the button
  const formError = error && !(isApiError(error) && error.status === 422) ? getErrorMessage(error) : null;

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    login({ email: email.trim(), password });
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <FormField label="Email" htmlFor="email" error={fieldErrors.email}>
        <input
          id="email"
          type="email"
          autoComplete="username"
          required
          value={email}
          onChange={(event) => setEmail(event.target.value)}
          className="form-input"
        />
      </FormField>

      <FormField label="Password" htmlFor="password" error={fieldErrors.password}>
        <input
          id="password"
          type="password"
          autoComplete="current-password"
          required
          value={password}
          onChange={(event) => setPassword(event.target.value)}
          className="form-input"
        />
      </FormField>

      {formError && (
        <p className="rounded-md bg-red-500/10 px-3 py-2 text-sm text-red-300" role="alert">
          {formError}
        </p>
      )}

      <button type="submit" disabled={isLoading} className="btn-primary w-full">
        {isLoading ? 'Logging in…' : 'Log in'}
      </button>
    </form>
  );
}
