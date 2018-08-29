<template>

    <button class="button"
        @click="toggle">
        <span class="icon is-small">
            <fa icon="check-square"/>
        </span>
    </button>

</template>

<script>

  import { library } from '@fortawesome/fontawesome-svg-core';
  import { faCheckSquare } from '@fortawesome/free-solid-svg-icons';

  library.add(faCheckSquare);

  export default {
    name: 'SelectAll',

    data() {
        return {
            state: false
        }
    },

    methods: {
      toggle() {
          this.state = !this.state;
          this.$emit('select-all', this.state)
      },
    },
  };

</script>
