import { useState } from 'react';
import { useGetPaginatedProjectsQuery, useGetProjectDetailsQuery } from '../../client/projectApi';
import SearchSelect from '../SharedComponents/SearchSelect';

interface ProjectSelectProps {
  id: string;
  value: number | null;
  onChange: (projectId: number) => void;
}

/** Choose a project: searchable (by project, company or client name), one page at a time. */
export default function ProjectSelect({ id, value, onChange }: ProjectSelectProps) {
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [pickedLabel, setPickedLabel] = useState<string>();

  const { data, isFetching } = useGetPaginatedProjectsQuery({ search, page });
  // The current project may not be on this page, so get its name on its own (when editing)
  const { data: current } = useGetProjectDetailsQuery(value ?? 0, { skip: value === null || pickedLabel !== undefined });

  return (
    <SearchSelect
      id={id}
      placeholder="Choose a project"
      selectedId={value}
      selectedLabel={pickedLabel ?? current?.name}
      options={(data?.data ?? []).map((project) => ({ id: project.id, label: project.name, hint: project.company_name }))}
      isLoading={isFetching}
      page={data?.meta.current_page ?? 1}
      lastPage={data?.meta.last_page ?? 1}
      onPageChange={setPage}
      onSearch={(term) => {
        setSearch(term);
        setPage(1);
      }}
      onSelect={(option) => {
        setPickedLabel(option.label);
        onChange(option.id);
      }}
    />
  );
}
