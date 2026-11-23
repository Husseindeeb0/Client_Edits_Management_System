import { useState } from 'react';
import type { ChangeEvent, FormEvent } from 'react';
import { useUpdateProfileMutation } from '../../client/authApi';
import { getErrorMessage, getFieldErrors, isApiError } from '../../errors/apiError';
import type { AuthUser } from '../../types/auth';
import { validateProfile } from '../../validators/profileValidator';
import type { ProfileFormValues } from '../../validators/profileValidator';
import FormField from '../SharedComponents/FormField';

interface ProfileFormProps {
  user: AuthUser;
}

export default function ProfileForm({ user }: ProfileFormProps) {
  const [values, setValues] = useState<ProfileFormValues>({ name: user.name, email: user.email });
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});
  const [isSaved, setIsSaved] = useState(false);

  const [updateProfile, { isLoading, error }] = useUpdateProfileMutation();

  const errors = { ...getFieldErrors(error), ...validationErrors };
  const formError = error && !(isApiError(error) && error.status === 422) ? getErrorMessage(error) : null;

  function handleChange(event: ChangeEvent<HTMLInputElement>) {
    setValues({ ...values, [event.target.name]: event.target.value });
    setIsSaved(false);
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const newErrors = validateProfile(values);
    setValidationErrors(newErrors);
    if (Object.keys(newErrors).length > 0) {
      return;
    }

    const result = await updateProfile({ name: values.name.trim(), email: values.email.trim() });

    setIsSaved(!result.error);
  }

  return (
    <form onSubmit={handleSubmit} noValidate className="space-y-4">
      <FormField label="Name" htmlFor="name" error={errors.name}>
        <input id="name" name="name" autoComplete="name" value={values.name} onChange={handleChange} className="form-input" />
      </FormField>

      <FormField label="Email" htmlFor="email" error={errors.email}>
        <input
          id="email"
          name="email"
          type="email"
          autoComplete="username"
          value={values.email}
          onChange={handleChange}
          className="form-input"
        />
      </FormField>

      {formError && (
        <p className="rounded-md bg-red-500/10 px-3 py-2 text-sm text-red-300" role="alert">
          {formError}
        </p>
      )}

      <div className="flex items-center justify-end gap-3">
        {isSaved && <p className="text-sm text-teal-400">Profile updated.</p>}
        <button type="submit" disabled={isLoading} className="btn-primary">
          Save changes
        </button>
      </div>
    </form>
  );
}
