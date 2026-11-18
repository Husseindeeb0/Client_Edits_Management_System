import { useState } from 'react';
import type { ChangeEvent, FormEvent } from 'react';
import { useGetPaginatedCompaniesQuery } from '../../client/companyApi';
import { useCreateProjectMutation, useUpdateProjectMutation } from '../../client/projectApi';
import { getErrorMessage, getFieldErrors, isApiError } from '../../errors/apiError';
import type { Project } from '../../types/project';
import { validateProject } from '../../validators/projectValidator';
import type { ProjectFormValues } from '../../validators/projectValidator';
import FormField from '../SharedComponents/FormField';

interface ProjectFormProps {
  /** Given = edit this project; left out = create a new one. */
  project?: Project;
  onSaved: () => void;
}

export default function ProjectForm({ project, onSaved }: ProjectFormProps) {
  const [values, setValues] = useState<ProjectFormValues>({
    name: project?.name ?? '',
    company_id: project ? String(project.company_id) : '',
  });
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

  // For the dropdown; 100 is the most the API returns per page
  const { data: companies } = useGetPaginatedCompaniesQuery({ per_page: 100 });

  const [createProject, createState] = useCreateProjectMutation();
  const [updateProject, updateState] = useUpdateProjectMutation();
  const { isLoading, error } = project ? updateState : createState;

  const errors = { ...getFieldErrors(error), ...validationErrors };
  const formError = error && !(isApiError(error) && error.status === 422) ? getErrorMessage(error) : null;

  function handleChange(event: ChangeEvent<HTMLInputElement | HTMLSelectElement>) {
    setValues({ ...values, [event.target.name]: event.target.value });
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const newErrors = validateProject(values);
    setValidationErrors(newErrors);
    if (Object.keys(newErrors).length > 0) {
      return;
    }

    const payload = { name: values.name.trim(), company_id: Number(values.company_id) };

    const result = project
      ? await updateProject({ id: project.id, changes: payload })
      : await createProject(payload);

    if (!result.error) {
      onSaved();
    }
  }

  return (
    <form onSubmit={handleSubmit} noValidate className="space-y-4">
      <FormField label="Name" htmlFor="name" error={errors.name}>
        <input id="name" name="name" value={values.name} onChange={handleChange} className="form-input" />
      </FormField>

      <FormField label="Company" htmlFor="company_id" error={errors.company_id}>
        <select id="company_id" name="company_id" value={values.company_id} onChange={handleChange} className="form-input">
          <option value="">{companies ? 'Choose a company' : 'Loading companies…'}</option>
          {companies?.data.map((company) => (
            <option key={company.id} value={company.id}>
              {company.name} ({company.client_name})
            </option>
          ))}
        </select>
      </FormField>

      {formError && (
        <p className="rounded-md bg-red-500/10 px-3 py-2 text-sm text-red-300" role="alert">
          {formError}
        </p>
      )}

      <div className="flex justify-end">
        <button type="submit" disabled={isLoading} className="btn-primary">
          {project ? 'Save changes' : 'Create project'}
        </button>
      </div>
    </form>
  );
}
