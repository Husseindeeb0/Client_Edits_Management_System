import { useState } from 'react';
import { useGetInfiniteProjectsInfiniteQuery, useGetProjectDetailsQuery } from '../../client/projectApi';
import SearchSelect from '../SharedComponents/SearchSelect';

interface ProjectSelectProps {
  id: string;
  value: number | null;
  onChange: (projectId: number) => void;
}

/** Choose a project: searchable (by project, company or client name), more projects load while scrolling. */
export default function ProjectSelect({ id, value, onChange }: ProjectSelectProps) {
  const [search, setSearch] = useState('');
  const [pickedLabel, setPickedLabel] = useState<string>();

  const { data, isFetching, isFetchingNextPage, hasNextPage, fetchNextPage } = useGetInfiniteProjectsInfiniteQuery(search);
  // The current project may not be loaded yet, so get its name on its own (when editing)
  const { data: current } = useGetProjectDetailsQuery(value ?? 0, { skip: value === null || pickedLabel !== undefined });

  return (
    <SearchSelect
      id={id}
      placeholder="Choose a project"
      selectedId={value}
      selectedLabel={pickedLabel ?? current?.name}
      options={(data?.pages ?? []).flatMap((page) =>
        page.data.map((project) => ({ id: project.id, label: project.name, hint: project.company_name })),
      )}
      isLoading={isFetching && !isFetchingNextPage}
      isLoadingMore={isFetchingNextPage}
      hasMore={hasNextPage}
      onLoadMore={fetchNextPage}
      onSearch={setSearch}
      onSelect={(option) => {
        setPickedLabel(option.label);
        onChange(option.id);
      }}
    />
  );
}
