import React from "react";
import { Button } from "@/components/ui/button";

const ArrowUp = () => (
  <svg
    height="16"
    strokeLinejoin="round"
    viewBox="0 0 16 16"
    width="16"
  >
    <path
      fillRule="evenodd"
      clipRule="evenodd"
      d="M8.70711 1.39644C8.31659 1.00592 7.68342 1.00592 7.2929 1.39644L2.21968 6.46966L1.68935 6.99999L2.75001 8.06065L3.28034 7.53032L7.25001 3.56065V14.25V15H8.75001V14.25V3.56065L12.7197 7.53032L13.25 8.06065L14.3107 6.99999L13.7803 6.46966L8.70711 1.39644Z"
    />
  </svg>
);

const ArrowLeft = () => (
  <svg
    height="16"
    strokeLinejoin="round"
    viewBox="0 0 16 16"
    width="16"
  >
    <path
      fillRule="evenodd"
      clipRule="evenodd"
      d="M6.46966 13.7803L6.99999 14.3107L8.06065 13.25L7.53032 12.7197L3.56065 8.75001H14.25H15V7.25001H14.25H3.56065L7.53032 3.28034L8.06065 2.75001L6.99999 1.68935L6.46966 2.21968L1.39644 7.2929C1.00592 7.68342 1.00592 8.31659 1.39644 8.70711L6.46966 13.7803Z"
      />
  </svg>
);

const ArrowRight = () => (
  <svg
    height="16"
    strokeLinejoin="round"
    viewBox="0 0 16 16"
    width="16"
  >
    <path
      fillRule="evenodd"
      clipRule="evenodd"
      d="M9.53033 2.21968L9 1.68935L7.93934 2.75001L8.46967 3.28034L12.4393 7.25001H1.75H1V8.75001H1.75H12.4393L8.46967 12.7197L7.93934 13.25L9 14.3107L9.53033 13.7803L14.6036 8.70711C14.9941 8.31659 14.9941 7.68342 14.6036 7.2929L9.53033 2.21968Z"
      />
  </svg>
);

export const Default = () => {
  return (
    <div className="grid grid-cols-3 gap-4">
      <Button type="primary">Primary</Button>
      <Button type="secondary">Secondary</Button>
      <Button type="tertiary">Tertiary</Button>
      <Button type="error">Error</Button>
      <Button type="warning">Warning</Button>
      <Button shape="rounded">Rounded</Button>
      <Button loading>Loading</Button>
      <Button disabled>Disabled</Button>
      <Button prefix={<ArrowLeft />}>With Icon</Button>
    </div>
  );
};

export const Sizes = () => {
  return (
    <div className="flex justify-between items-center gap-2">
      <Button size="small" type="primary">Upload</Button>
      <Button type="primary">Upload</Button>
      <Button size="large" type="primary">Upload</Button>
    </div>
  );
};

export const Types = () => {
  return (
    <div className="flex justify-between items-center gap-2">
      <Button type="secondary">Upload</Button>
      <Button type="tertiary">Upload</Button>
      <Button type="error">Upload</Button>
      <Button type="warning">Upload</Button>
    </div>
  );
};

export const Shapes = () => {
  return (
    <div className="flex justify-between items-center gap-2">
      <Button aria-label="Upload" shape="square" size="tiny" svgOnly type="primary">
        <ArrowUp />
      </Button>
      <Button aria-label="Upload" shape="square" size="small" svgOnly type="primary">
        <ArrowUp />
      </Button>
      <Button aria-label="Upload" shape="square" svgOnly type="primary">
        <ArrowUp />
      </Button>
      <Button aria-label="Upload" shape="square" size="large" svgOnly type="primary">
        <ArrowUp />
      </Button>
      <Button aria-label="Upload" shape="circle" size="tiny" svgOnly type="primary">
        <ArrowUp />
      </Button>
      <Button aria-label="Upload" shape="circle" size="small" svgOnly type="primary">
        <ArrowUp />
      </Button>
      <Button aria-label="Upload" shape="circle" svgOnly type="primary">
        <ArrowUp />
      </Button>
      <Button aria-label="Upload" shape="circle" size="large" svgOnly type="primary">
        <ArrowUp />
      </Button>
    </div>
  );
};

export const PrefixSuffix = () => {
  return (
    <div className="flex justify-between items-center gap-2">
      <Button prefix={<ArrowLeft />} type="primary">Upload</Button>
      <Button suffix={<ArrowRight />} type="primary">Upload</Button>
      <Button prefix={<ArrowLeft />} suffix={<ArrowRight />} type="primary">
        Upload
      </Button>
    </div>
  );
};

export const Rounded = () => {
  return (
    <div className="flex justify-between items-center gap-2">
      <Button shadow shape="rounded" size="small" type="secondary">
        Upload
      </Button>
      <Button shadow shape="rounded" type="secondary">
        Upload
      </Button>
      <Button shadow shape="rounded" size="large" type="secondary">
        Upload
      </Button>
    </div>
  );
};

export const Loading = () => {
  return (
    <div className="flex justify-between items-center gap-2">
      <Button loading size="small" type="primary">Upload</Button>
      <Button loading type="primary">Upload</Button>
      <Button loading size="large" type="primary">Upload</Button>
    </div>
  );
};

export const Disabled = () => {
  return (
    <div className="flex justify-between items-center gap-2">
      <Button disabled size="small" type="primary">Upload</Button>
      <Button disabled type="primary">Upload</Button>
      <Button disabled size="large" type="primary">Upload</Button>
    </div>
  );
};

export default function ButtonDemo() {
  return (
    <div className="p-8 space-y-8 max-w-4xl mx-auto">
      <h2 className="text-xl font-bold">Button Component Demos</h2>
      <section>
        <h3 className="text-lg font-semibold mb-4">Default Grid</h3>
        <Default />
      </section>
      <section>
        <h3 className="text-lg font-semibold mb-4">Sizes</h3>
        <Sizes />
      </section>
      <section>
        <h3 className="text-lg font-semibold mb-4">Types</h3>
        <Types />
      </section>
      <section>
        <h3 className="text-lg font-semibold mb-4">Shapes</h3>
        <Shapes />
      </section>
      <section>
        <h3 className="text-lg font-semibold mb-4">Prefix & Suffix</h3>
        <PrefixSuffix />
      </section>
      <section>
        <h3 className="text-lg font-semibold mb-4">Rounded with Shadow</h3>
        <Rounded />
      </section>
      <section>
        <h3 className="text-lg font-semibold mb-4">Loading</h3>
        <Loading />
      </section>
      <section>
        <h3 className="text-lg font-semibold mb-4">Disabled</h3>
        <Disabled />
      </section>
    </div>
  );
}
